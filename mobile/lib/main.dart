import 'package:flutter/material.dart';
import 'core/app_theme.dart';
import 'screens/citizen/citizen_home_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const AmarMayorApp());
}

class AmarMayorApp extends StatelessWidget {
  const AmarMayorApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'আমার মেয়র — Amar Mayor',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      home: const CitizenHomeScreen(),
    );
  }
}
