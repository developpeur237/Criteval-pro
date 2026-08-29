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
);
