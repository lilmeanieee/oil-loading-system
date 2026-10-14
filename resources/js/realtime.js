// Live notifications: only starts when the page has the bell (Admin portal).
const el = document.querySelector('[data-realtime-user]');

if (el) {
    Promise.all([import('laravel-echo'), import('pusher-js')])
        .then(([echoModule, pusherModule]) => {
            const Echo = echoModule.default;
            window.Pusher = pusherModule.default;

            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: import.meta.env.VITE_REVERB_APP_KEY,
                wsHost: import.meta.env.VITE_REVERB_HOST,
                wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
                wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
                forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
                enabledTransports: ['ws', 'wss'],
            });

            window.Echo.private('App.Models.User.' + el.dataset.realtimeUser).notification((n) => {
                window.dispatchEvent(new CustomEvent('notification-received', { detail: n }));
            });
        })
        .catch((e) => console.warn('Live notifications are not available:', e));
}