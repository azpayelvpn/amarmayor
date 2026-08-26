import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class OfflineSyncManager {
  static const String _queueKey = 'amarmayor_offline_action_queue';

  static Future<void> queueAction(String actionType, Map<String, dynamic> data) async {
    final prefs = await SharedPreferences.getInstance();
    final List<String> current = prefs.getStringList(_queueKey) ?? [];
    
    final action = {
      'action_type': actionType,
      'data': data,
      'timestamp': DateTime.now().toIso8601String(),
    };
    
    current.add(jsonEncode(action));
    await prefs.setStringList(_queueKey, current);
  }

  static Future<List<Map<String, dynamic>>> getPendingActions() async {
    final prefs = await SharedPreferences.getInstance();
    final List<String> current = prefs.getStringList(_queueKey) ?? [];
    return current.map((s) => jsonDecode(s) as Map<String, dynamic>).toList();
  }

  static Future<void> clearQueue() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_queueKey);
  }
}
