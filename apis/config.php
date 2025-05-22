<?php
// Supabase connection details
$supabaseUrl = "https://ydjrvlauamvfabaczirp.supabase.co";
$supabaseKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlkanJ2bGF1YW12ZmFiYWN6aXJwIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDc3NjU0MzEsImV4cCI6MjA2MzM0MTQzMX0.uNf6XBAUQ6u3R4GaFhSCMuXVejWNgrkj5Xxexi6KJos";

function disableSSLVerification($ch) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    return $ch;
}
