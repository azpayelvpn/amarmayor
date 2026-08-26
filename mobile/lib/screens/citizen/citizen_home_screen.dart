import 'package:flutter/material.dart';
import '../../core/app_theme.dart';
import 'complaint_submission_screen.dart';
import 'complaint_tracking_screen.dart';

class CitizenHomeScreen extends StatelessWidget {
  const CitizenHomeScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('আমার মেয়র — ময়মনসিংহ সিটি'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Banner
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [AppTheme.primaryGreen, Color(0xFF004D38)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Text(
                    'নাগরিক সেবা ও অভিযোগ প্রতিকার',
                    style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                  SizedBox(height: 8),
                  Text(
                    'আপনার এলাকার সড়ক বাতি, বর্জ্য বা ড্রেনেজ সমস্যার দ্রুত সমাধানে সরাসরি অভিযোগ দাখিল করুন।',
                    style: TextStyle(color: Colors.white70, fontSize: 14),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Quick Actions
            Row(
              children: [
                Expanded(
                  child: _ActionCard(
                    icon: Icons.add_a_photo_outlined,
                    label: 'নতুন অভিযোগ',
                    color: AppTheme.primaryGreen,
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const ComplaintSubmissionScreen()),
                      );
                    },
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _ActionCard(
                    icon: Icons.search,
                    label: 'অভিযোগ ট্র্যাকিং',
                    color: const Color(0xFF0284C7),
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const ComplaintTrackingScreen()),
                      );
                    },
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Emergency Contacts
            const Text(
              'জরুরি পৌর কন্ট্রোল রুম',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.darkSlate),
            ),
            const SizedBox(height: 12),
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              child: const ListTile(
                leading: Icon(Icons.phone_in_talk, color: AppTheme.accentRed, size: 32),
                title: Text('হটলাইন: ১৬১০৬ / ০২৯৯৬৬-৬৩১২৩'),
                subtitle: Text('ময়মনসিংহ সিটি কর্পোরেশন কেন্দ্রীয় নিয়ন্ত্রণ কক্ষ'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ActionCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _ActionCard({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFFE2E8F0)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          children: [
            CircleAvatar(
              radius: 26,
              backgroundColor: color.withOpacity(0.12),
              child: Icon(icon, color: color, size: 28),
            ),
            const SizedBox(height: 12),
            Text(
              label,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppTheme.darkSlate),
            ),
          ],
        ),
      ),
    );
  }
}
