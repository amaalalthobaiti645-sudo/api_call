# Voice AI Assistant

A browser-based voice assistant: it listens to the user's voice, sends the
transcribed text to a PHP backend that securely calls the Groq API, then
displays and speaks the reply.
<img width="984" height="674" alt="image" src="https://github.com/user-attachments/assets/2ff6cadf-26c5-4e70-9040-73cad9c74ca4" />


## Structure

```
/ (htdocs)
├── index.html
├── style.css
├── app.js
├── api_call.php   # Calls the Groq API
├── config.php      # Groq API key (protected by .htaccess)
└── .htaccess
```

## Setup

1. Upload all files to the server root (no subfolder).
2. Add your real Groq API key in `config.php` **on the server only** —
   never commit it to a public repo.
3. Use Chrome or Edge for best speech-recognition support.

## Bugs Found & Fixed

1. **`app.js` pointed to the wrong backend path** (`api/chat.php` instead
   of `api_call.php`), causing every request to fail with a 404 — the
   actual cause of the "connection error" message. The backend file was
   originally named `chat.php` inside an `api/` folder, but InfinityFree's
   security system was blocking that path with 403 errors, so it was
   renamed and moved to `api_call.php` in the root — a correct fix, except
   `app.js` was never updated to match the new name.
2. **`.htaccess` used Apache 2.2-only syntax**, so `config.php` could stay
   publicly accessible on Apache 2.4 hosts. Added `Require all denied` for
   compatibility with both versions.
3. **Model `llama-3.3-70b-versatile` is deprecated** by Groq (shutdown
   08/16/2026). Switched to `openai/gpt-oss-120b`.

After these fixes, the full chain (`app.js` → `api_call.php` → Groq API)
works end to end.
