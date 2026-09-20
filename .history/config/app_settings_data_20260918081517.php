<?php
return array (
  'application_name' => 'Criteval_pro',
  'organization' => 'ISA - IMPACT SANTE AFRIQUE',
  'contact_email' => 'contact@criteval.pro',
  'website' => 'https://criteval.pro',
  'modules' => 
  array (
    'apercu' => false,
    'projets' => false,
    'criteres' => true,
    'formulaires' => true,
    'evaluations' => true,
  ),
  'smtp' => 
  array (
    'profile' => 'hostinger',
    'transport' => 'auto',
    'host' => 'smtp.hostinger.com',
    'port' => 587,
    'username' => 'noreply@criteval.pro',
    'password' => '',
    'security' => 'tls',
    'from' => 'noreply@criteval.pro',
    'sender' => 'Criteval Pro',
    'timeout' => 15,
  ),
  'security' => 
  array (
    'session_timeout' => 60,
    'otp_attempts' => 3,
    'logs_enabled' => true,
    'two_factor_enabled' => false,
    'csrf_protection' => true,
  ),
  'notifications' => 
  array (
    'enabled' => true,
    'schedule_alerts' => true,
    'browser_alerts' => false,
    'sound_alerts' => true,
    'lead_minutes' => 1440,
    'training_lead_days' => 7,
    'custom' => 
    array (
      0 => 
      array (
        'id' => '95cc7b1026f8e4ca',
        'title' => 'Brainstorming',
        'message' => 'Reunion de brainstorming dans 30mins',
        'due_at' => '2026-09-18 07:59:00',
        'severity' => 'urgent',
        'created_at' => '2026-09-18 15:58:05',
      ),
      1 => 
      array (
        'id' => '04c0780c19911970',
        'title' => 'Test de responsabiliter',
        'message' => 'ceci est un test de responsabiliter des notification.',
        'due_at' => '2026-09-24 08:05:00',
        'severity' => 'info',
        'created_at' => '2026-09-18 16:04:06',
      ),
    ),
  ),
);
