<span class="footer-social-icon" aria-hidden="true">
    @switch($platform)
        @case('WhatsApp')
            <svg viewBox="0 0 24 24" fill="none"><path d="M19.4 4.7A10 10 0 0 0 3.7 16.8L2.5 21l4.3-1.1A10 10 0 1 0 19.4 4.7Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.3 7.8c.3-.6.6-.6 1-.6h.4c.2 0 .4.1.5.4l.8 1.8c.1.2.1.4-.1.6l-.6.7c-.2.2-.2.4 0 .7.6 1 1.4 1.8 2.4 2.4.3.2.5.2.7-.1l.7-.8c.2-.2.4-.3.7-.2l1.7.8c.3.1.4.3.4.5 0 .7-.3 1.3-.8 1.7-.5.4-1.2.6-2 .4-1.2-.3-2.8-1.1-4.1-2.4-1.3-1.2-2.1-2.7-2.4-3.8-.3-.9 0-1.6.7-2.1Z" fill="currentColor"/></svg>
            @break
        @case('Instagram')
            <svg viewBox="0 0 24 24" fill="none"><rect x="3.2" y="3.2" width="17.6" height="17.6" rx="5" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.7"/><circle cx="17.7" cy="6.6" r="1.1" fill="currentColor"/></svg>
            @break
        @case('Facebook')
            <svg viewBox="0 0 24 24" fill="none"><path d="M13.6 21v-8h2.8l.4-3.2h-3.2V7.7c0-.9.3-1.5 1.6-1.5H17V3.3c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.3H7.5V13h2.8v8h3.3Z" fill="currentColor"/></svg>
            @break
        @case('TikTok')
            <svg viewBox="0 0 24 24" fill="none"><path d="M14.2 3.5h3c.2 1.7 1.1 3.1 2.8 3.8v3a8 8 0 0 1-2.8-1v5.4a6.3 6.3 0 1 1-6.3-6.3c.4 0 .8 0 1.2.1v3.2a3.1 3.1 0 1 0 2.1 3V3.5Z" fill="currentColor"/></svg>
            @break
        @case('YouTube')
            <svg viewBox="0 0 24 24" fill="none"><path d="M21 8.1a2.6 2.6 0 0 0-1.8-1.8C17.6 5.9 12 5.9 12 5.9s-5.6 0-7.2.4A2.6 2.6 0 0 0 3 8.1a27 27 0 0 0-.4 3.9A27 27 0 0 0 3 15.9a2.6 2.6 0 0 0 1.8 1.8c1.6.4 7.2.4 7.2.4s5.6 0 7.2-.4a2.6 2.6 0 0 0 1.8-1.8 27 27 0 0 0 .4-3.9 27 27 0 0 0-.4-3.9Z" stroke="currentColor" stroke-width="1.5"/><path d="m10 9 5 3-5 3V9Z" fill="currentColor"/></svg>
            @break
        @case('LinkedIn')
            <svg viewBox="0 0 24 24" fill="none"><path d="M5 9v10M5 5.5v.1M10 19v-6c0-2 1.2-3.3 3-3.3s3 1.3 3 3.3v6M10 10v9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="5" cy="5.5" r="1.2" fill="currentColor"/></svg>
            @break
        @case('X')
            <svg viewBox="0 0 24 24" fill="none"><path d="m4 4 16 16M20 4 4 20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M4 4h4l12 16h-4L4 4Z" fill="currentColor" opacity=".18"/></svg>
            @break
    @endswitch
</span>