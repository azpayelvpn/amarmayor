import 'package:flutter/material.dart';
import '../../core/app_theme.dart';

class FieldTasksScreen extends StatelessWidget {
  const FieldTasksScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('মাঠ পর্যায়ের দায়িত্ব ও কাজ')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _TaskCard(
            taskCode: 'TSK-2608-00101',
            service: 'ড্রেনেজ পরিষ্কার ও পলি অপসারণ',
            location: 'ওয়ার্ড নং ৫, গোলপুকুর মোড়',
            status: 'চলমান (In Progress)',
            onStart: () {},
            onComplete: () {},
          ),
          const SizedBox(height: 12),
          _TaskCard(
            taskCode: 'TSK-2608-00102',
            service: 'সড়ক বাতি মেরামত ও বাল্ব প্রতিস্থাপন',
            location: 'ওয়ার্ড নং ১২, বাউন্ডারি রোড',
            status: 'অপেক্ষমাণ (Assigned)',
            onStart: () {},
            onComplete: () {},
          ),
        ],
      ),
    );
  }
}

class _TaskCard extends StatelessWidget {
  final String taskCode;
  final String service;
  final String location;
  final String status;
  final VoidCallback onStart;
  final VoidCallback onComplete;

  const _TaskCard({
    required this.taskCode,
    required this.service,
    required this.location,
    required this.status,
    required this.onStart,
    required this.onComplete,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(taskCode, style: const TextStyle(fontWeight: FontWeight.bold, color: AppTheme.primaryGreen)),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(color: const Color(0xFFE2E8F0), borderRadius: BorderRadius.circular(6)),
                  child: Text(status, style: const TextStyle(fontSize: 12)),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(service, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
            Text(location, style: const TextStyle(color: Colors.grey)),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    icon: const Icon(Icons.camera_alt),
                    label: const Text('প্রমাণ ছবি'),
                    onPressed: () {},
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: ElevatedButton(
                    onPressed: onComplete,
                    child: const Text('কাজ সম্পন্ন'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
