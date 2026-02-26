<?php
// Database configuration values for MySQL connection
const DB_HOST = 'localhost';
const DB_NAME = 'laporan_bmkg';
const DB_USER = 'root';
const DB_PASS = '';
const BASE_URL = '/laporan_bmkg';

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);
session_name('bmkg_session');
