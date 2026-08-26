import 'package:flutter/material.dart';
import '../../core/app_theme.dart';

class MayorSnapshotScreen extends StatelessWidget {
  const MayorSnapshotScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('মেয়র কমান্ড স্ন্যাপশট')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('দৈনিক নির্বাহী সারসংক্ষেপ (২৪ ঘণ্টা)', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 1.5,
              children: const [
                _KpiBox(title: 'নতুন অভিযোগ', value: '২৮', color: AppTheme.primaryGreen),
                _KpiBox(title: 'নিষ্পত্তি সম্পন্ন', value: '২৪', color: Color(0xFF0284C7)),
                _KpiBox(title: 'সময়সীমা উত্তীর্ণ', value: '৩', color: AppTheme.accentRed),
                _KpiBox(title: 'নাগরিক সন্তুষ্টি', value: '৯৪%', color: Color(0xFF10B981)),
              ],
            ),
            const SizedBox(height: 20),
            const Text('জরুরি দৃষ্টি আকর্ষণ (Attention Queue)', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Card(
              color: const Color(0xFFFEF2F2),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
                side: const BorderSide(color: Color(0xFFFECACA)),
              ),
              child: const ListTile(
                leading: Icon(Icons.warning_amber_rounded, color: AppTheme.accentRed, size: 30),
                title: Text('MCC-2608-00042 — ড্রেনেজ উপচে পড়া'),
                subtitle: Text('প্রথম সময়সীমা উত্তীর্ণ (Overdue) — ওয়ার্ড নং ৩'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _KpiBox extends StatelessWidget {
  final String title;
  final String value;
  final Color color;

  const _KpiBox({required this.title, required this.value, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(fontSize: 13, color: Colors.grey)),
          const SizedBox(height: 4),
          Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
        ],
      ),
    );
  }
}
