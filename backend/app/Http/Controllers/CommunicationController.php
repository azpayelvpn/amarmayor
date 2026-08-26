<?php

declare(strict_types=1);

namespace AmarMayor\Http\Controllers;

use AmarMayor\Auth\Auth;
use AmarMayor\Domain\Communication\CommunicationService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;

class CommunicationController
{
    private CommunicationService $communicationService;

    public function __construct(?CommunicationService $communicationService = null)
    {
        $this->communicationService = $communicationService ?: new CommunicationService();
    }

    public function sendMessage(Request $request, ?string $id = null): Response
    {
        $complaintId = (int)($id ?: $request->getAttribute('id', 0));
        $user = Auth::user();
        if (!$user) {
            return Response::error('UNAUTHORIZED', 'Authentication required', 401);
        }

        $body = $request->input('body', '');
        $type = (string)$request->input('message_type', 'inquiry');

        if (empty(trim((string)$body))) {
            return Response::error('VALIDATION_ERROR', 'Message body is required', 422);
        }

        $msgId = $this->communicationService->sendComplaintMessage($complaintId, $user->id, (string)$body, $type);
        return Response::json(['message_id' => $msgId, 'status' => 'sent'], 201);
    }

    public function getMessages(Request $request, ?string $id = null): Response
    {
        $complaintId = (int)($id ?: $request->getAttribute('id', 0));
        $messages = $this->communicationService->getComplaintMessages($complaintId);
        return Response::json($messages);
    }

    public function addNote(Request $request, ?string $id = null): Response
    {
        $complaintId = (int)($id ?: $request->getAttribute('id', 0));
        $user = Auth::user();
        if (!$user) {
            return Response::error('UNAUTHORIZED', 'Authentication required', 401);
        }

        $text = $request->input('note_text', '');
        $type = (string)$request->input('note_type', 'internal_memo');

        if (empty(trim((string)$text))) {
            return Response::error('VALIDATION_ERROR', 'Note text is required', 422);
        }

        $noteId = $this->communicationService->addInternalNote($complaintId, $user->id, (string)$text, $type);
        return Response::json(['note_id' => $noteId, 'status' => 'recorded'], 201);
    }

    public function sendOfficeMessage(Request $request): Response
    {
        $user = Auth::user();
        if (!$user) {
            return Response::error('UNAUTHORIZED', 'Authentication required', 401);
        }

        $officeType = (string)$request->input('office_type', 'mayor_office');
        $category = (string)$request->input('category', 'general');
        $body = (string)$request->input('body', '');
        $subject = $request->input('subject');
        $linkedComplaintId = $request->input('linked_complaint_id') !== null ? (int)$request->input('linked_complaint_id') : null;

        if (empty(trim($body))) {
            return Response::error('VALIDATION_ERROR', 'Message body is required', 422);
        }

        $msgId = $this->communicationService->sendOfficeMessage($user->id, $officeType, $category, $body, $subject, $linkedComplaintId);
        return Response::json(['office_message_id' => $msgId, 'status' => 'received'], 201);
    }
}
