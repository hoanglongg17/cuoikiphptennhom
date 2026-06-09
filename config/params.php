<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    
    'geminiApiKey' => getenv('GEMINI_API_KEY') ?: 'YOUR_API_KEY',
    'geminiModel' => getenv('GEMINI_MODEL') ?: 'gemini-2.5-flash',
];