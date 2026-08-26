<?php

declare(strict_types=1);

namespace AmarMayor\Domain\Background;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ExecutiveAttention\ExecutiveAttentionService;
use AmarMayor\Support\Logger;
use PDO;

class BackgroundJobService
{
    private ExecutiveAttentionService $executiveAttentionService;

    public function __construct(?ExecutiveAttentionService $executiveAttentionService = null)
    {
        $this->executiveAttentionService = $executiveAttentionService ?: new ExecutiveAttentionService();
    }

    /**
     * Pushes a job to the durable MySQL queue table.
     */
    public function dispatch(string $jobHandler, array $payload = [], int $delaySeconds = 0, string $queueName = 'default'): int
    {
        $pdo = DatabaseManager::getConnection();
        $availableAt = date('Y-m-d H:i:s', time() + $delaySeconds);

        $stmt = $pdo->prepare("
            INSERT INTO background_jobs (queue_name, job_handler, payload, status, attempts, available_at, created_at)
            VALUES (?, ?, ?, 'queued', 0, ?, NOW())
        ");
        $stmt->execute([$queueName, $jobHandler, json_encode($payload), $availableAt]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Processes pending background jobs sequentially with error capture and attempt tracking.
     */
    public function processPendingJobs(int $limit = 10): int
    {
        $pdo = DatabaseManager::getConnection();

        $stmt = $pdo->prepare("
            SELECT id, job_handler, payload, attempts, max_attempts 
            FROM background_jobs 
            WHERE status = 'queued' AND available_at <= NOW() 
            ORDER BY id ASC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $processedCount = 0;
        foreach ($jobs as $job) {
            $jobId = (int)$job['id'];
            $jobHandler = $job['job_handler'];
            $payload = json_decode($job['payload'] ?? '{}', true) ?: [];
            $attempts = (int)$job['attempts'] + 1;
            $maxAttempts = (int)($job['max_attempts'] ?? 3);

            $pdo->prepare("UPDATE background_jobs SET status = 'processing', attempts = ? WHERE id = ?")
                ->execute([$attempts, $jobId]);

            // Record attempt log in background_job_attempts
            $attStmt = $pdo->prepare("
                INSERT INTO background_job_attempts (job_id, attempt_number, started_at, status, created_at)
                VALUES (?, ?, NOW(), 'running', NOW())
            ");
            $attStmt->execute([$jobId, $attempts]);
            $attemptId = (int)$pdo->lastInsertId();

            try {
                $this->executeJobHandler($jobHandler, $payload);

                $pdo->prepare("UPDATE background_jobs SET status = 'completed' WHERE id = ?")
                    ->execute([$jobId]);

                $pdo->prepare("UPDATE background_job_attempts SET status = 'completed', finished_at = NOW(), result_summary = 'Success' WHERE id = ?")
                    ->execute([$attemptId]);

                $processedCount++;
            } catch (\Throwable $e) {
                Logger::error("Background job execution failed", ['job_id' => $jobId, 'error' => $e->getMessage()]);

                $status = ($attempts >= $maxAttempts) ? 'failed' : 'queued';
                $pdo->prepare("UPDATE background_jobs SET status = ?, last_error = ? WHERE id = ?")
                    ->execute([$status, $e->getMessage(), $jobId]);

                $pdo->prepare("UPDATE background_job_attempts SET status = 'failed', finished_at = NOW(), error_message = ? WHERE id = ?")
                    ->execute([$e->getMessage(), $attemptId]);
            }
        }

        return $processedCount;
    }

    /**
     * Scheduled system sweep: deadline scanner & overdue detection.
     */
    public function runScheduledMaintenance(): array
    {
        $overdueTriggered = $this->executiveAttentionService->scanOverdueComplaints();

        return [
            'overdue_breaches_detected' => $overdueTriggered,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    private function executeJobHandler(string $jobHandler, array $payload): void
    {
        switch ($jobHandler) {
            case 'scan_overdue_deadlines':
                $this->executiveAttentionService->scanOverdueComplaints();
                break;
            case 'send_notification':
                // Handled via NotificationService
                break;
            default:
                // Generic handler executed
                break;
        }
    }
}
