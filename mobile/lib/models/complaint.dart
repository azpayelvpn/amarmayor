class Complaint {
  final int id;
  final String publicComplaintNumber;
  final String citizenStatus;
  final String categoryNameBn;
  final String subcategoryNameBn;
  final int wardNumber;
  final String? publicSafeAddress;
  final String submittedAt;
  final String? deadlineAt;
  final int reopenCount;

  Complaint({
    required this.id,
    required this.publicComplaintNumber,
    required this.citizenStatus,
    required this.categoryNameBn,
    required this.subcategoryNameBn,
    required this.wardNumber,
    this.publicSafeAddress,
    required this.submittedAt,
    this.deadlineAt,
    this.reopenCount = 0,
  });

  factory Complaint.fromJson(Map<String, dynamic> json) {
    return Complaint(
      id: json['id'] as int,
      publicComplaintNumber: json['public_complaint_number'] as String,
      citizenStatus: json['citizen_status'] as String,
      categoryNameBn: json['category_name_bn'] as String? ?? 'পৌর সেবা',
      subcategoryNameBn: json['subcategory_name_bn'] as String? ?? 'অভিযোগ',
      wardNumber: json['ward_number'] as int? ?? 1,
      publicSafeAddress: json['public_safe_address'] as String?,
      submittedAt: json['submitted_at'] as String? ?? '',
      deadlineAt: json['deadline_at'] as String?,
      reopenCount: json['reopen_count'] as int? ?? 0,
    );
  }
}
