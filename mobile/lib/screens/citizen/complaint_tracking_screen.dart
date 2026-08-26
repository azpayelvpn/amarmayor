import 'package:flutter/material.dart';
import '../../core/app_theme.dart';
import '../../core/api_client.dart';
import '../../models/complaint.dart';

class ComplaintTrackingScreen extends StatefulWidget {
  const ComplaintTrackingScreen({Key? key}) : super(key: key);

  @override
  State<ComplaintTrackingScreen> createState() => _ComplaintTrackingScreenState();
}

class _ComplaintTrackingScreenState extends State<ComplaintTrackingScreen> {
  final _trackingController = TextEditingController();
  Complaint? _result;
  bool _isLoading = false;
  String? _errorMessage;

  Future<void> _trackComplaint() async {
    final query = _trackingController.text.trim();
    if (query.isEmpty) return;

    setState(() {
      _isLoading = true;
      _errorMessage = null;
      _result = null;
    });

    try {
      final res = await ApiClient.get('/complaints/track/$query');
      if (res['data'] != null) {
        setState(() {
          _result = Complaint.fromJson(res['data'] as Map<String, dynamic>);
        });
      } else {
        setState(() => _errorMessage = 'অভিযোগটি পাওয়া যায়নি');
      }
    } catch (e) {
      setState(() => _errorMessage = 'অভিযোগ খুঁজে পাওয়া যায়নি বা ভুল নম্বর।');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('অভিযোগ ট্র্যাকিং')),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _trackingController,
                    decoration: const InputDecoration(
                      hintText: 'MCC-YYMM-XXXXX',
                      labelText: 'ট্র্যাকিং নম্বর লিখুন',
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                ElevatedButton(
                  onPressed: _isLoading ? null : _trackComplaint,
                  child: const Icon(Icons.search),
                ),
              ],
            ),
            const SizedBox(height: 20),
            if (_isLoading) const CircularProgressIndicator(),
            if (_errorMessage != null)
              Text(_errorMessage!, style: const TextStyle(color: AppTheme.accentRed)),
            if (_result != null) ...[
              Card(
                elevation: 3,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'নম্বর: ${_result!.publicComplaintNumber}',
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.primaryGreen),
                      ),
                      const Divider(),
                      Text('সেবা: ${_result!.categoryNameBn} — ${_result!.subcategoryNameBn}'),
                      Text('ওয়ার্ড: ওয়ার্ড নং ${_result!.wardNumber}'),
                      Text('বর্তমান অবস্থা: ${_result!.citizenStatus}'),
                      if (_result!.publicSafeAddress != null)
                        Text('এলাকা: ${_result!.publicSafeAddress!}'),
                      if (_result!.reopenCount > 0)
                        Text('পুনর্বার কাজের অনুরোধ: ${_result!.reopenCount} বার', style: const TextStyle(color: AppTheme.accentRed)),
                    ],
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
