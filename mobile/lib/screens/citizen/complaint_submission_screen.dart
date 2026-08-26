import 'package:flutter/material.dart';
import '../../core/app_theme.dart';
import '../../core/api_client.dart';

class ComplaintSubmissionScreen extends StatefulWidget {
  const ComplaintSubmissionScreen({Key? key}) : super(key: key);

  @override
  State<ComplaintSubmissionScreen> createState() => _ComplaintSubmissionScreenState();
}

class _ComplaintSubmissionScreenState extends State<ComplaintSubmissionScreen> {
  final _formKey = GlobalKey<FormState>();
  int _selectedCategory = 1;
  int _selectedSubcategory = 1;
  int _selectedWard = 1;
  final _descriptionController = TextEditingController();
  final _landmarkController = TextEditingController();
  bool _isSubmitting = false;

  Future<void> _submitComplaint() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);
    try {
      final response = await ApiClient.post('/complaints', {
        'category_id': _selectedCategory,
        'subcategory_id': _selectedSubcategory,
        'ward_id': _selectedWard,
        'description': _descriptionController.text,
        'landmark': _landmarkController.text,
      });

      if (mounted) {
        final trackingNumber = response['data']?['public_complaint_number'] ?? 'MCC-NEW';
        showDialog(
          context: context,
          builder: (_) => AlertDialog(
            title: const Text('অভিযোগ সফলভাবে গৃহীত হয়েছে'),
            content: Text('আপনার ট্র্যাকিং নম্বর: $trackingNumber\nশীঘ্রই সংশ্লিষ্ট পৌর দল কার্যক্রম শুরু করবে।'),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.pop(context);
                },
                child: const Text('ঠিক আছে'),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('ত্রুটি: ${e.toString()}'), backgroundColor: AppTheme.accentRed),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('নতুন অভিযোগ দাখিল')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text('সেবা ও সমস্যার ধরন', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              const SizedBox(height: 8),
              DropdownButtonFormField<int>(
                value: _selectedCategory,
                decoration: const InputDecoration(labelText: 'সেবার বিভাগ'),
                items: const [
                  DropdownMenuItem(value: 1, child: Text('বর্জ্য ব্যবস্থাপনা')),
                  DropdownMenuItem(value: 2, child: Text('সড়ক বাতি ও বিদ্যুৎ')),
                  DropdownMenuItem(value: 3, child: Text('ড্রেনেজ ও জলাবদ্ধতা')),
                  DropdownMenuItem(value: 4, child: Text('সড়ক ও অবকাঠামো')),
                ],
                onChanged: (val) => setState(() => _selectedCategory = val ?? 1),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<int>(
                value: _selectedWard,
                decoration: const InputDecoration(labelText: 'ওয়ার্ড নম্বর'),
                items: List.generate(
                  33,
                  (i) => DropdownMenuItem(value: i + 1, child: Text('ওয়ার্ড নং ${i + 1}')),
                ),
                onChanged: (val) => setState(() => _selectedWard = val ?? 1),
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _landmarkController,
                decoration: const InputDecoration(
                  labelText: 'নিকটবর্তী ল্যান্ডমার্ক / চত্বর / মোড়',
                  hintText: 'যেমন: নতুন বাজার জামে মসজিদ সংলগ্ন',
                ),
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _descriptionController,
                maxLines: 4,
                decoration: const InputDecoration(
                  labelText: 'সমস্যার বিস্তারিত বিবরণ',
                  hintText: 'সমস্যাটি সম্পর্কে বিস্তারিত লিখুন...',
                ),
                validator: (val) => (val == null || val.trim().isEmpty) ? 'অনুগ্রহ করে বিবরণ লিখুন' : null,
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: _isSubmitting ? null : _submitComplaint,
                child: _isSubmitting
                    ? const CircularProgressIndicator(color: Colors.white)
                    : const Text('অভিযোগ জমা দিন'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
