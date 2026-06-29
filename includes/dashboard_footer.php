</div><!-- Close .dashboard-wrapper (opened in dashboard_header.php) -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Global Chat Unread Badges Polling Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const unreadBadge = document.getElementById('unread-chat-count');
    if (!unreadBadge) return;

    function fetchUnreadCount() {
        // Find base path dynamically (checks if page is in pages/ directory or root)
        const isRoot = window.location.pathname.indexOf('/pages/') === -1;
        const handlerUrl = isRoot ? 'pages/message_handler.php?action=unread_total' : 'message_handler.php?action=unread_total';

        fetch(handlerUrl)
            .then(response => response.json())
            .then(data => {
                if (data && data.status === 'success' && data.unread_count > 0) {
                    unreadBadge.innerText = data.unread_count;
                    unreadBadge.classList.remove('d-none');
                } else {
                    unreadBadge.classList.add('d-none');
                }
            })
            .catch(err => console.error('Unread count fetch error:', err));
    }

    // Run immediately and then poll every 30 seconds (optimized for shared hosting)
    fetchUnreadCount();
    setInterval(fetchUnreadCount, 30000);

    // Global Form Submit Spinner & Anti-Double Submission Protection
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (form.checkValidity ? form.checkValidity() : true) {
                const btn = form.querySelector('button[type="submit"]');
                if (btn && !btn.classList.contains('no-spinner')) {
                    setTimeout(() => {
                        btn.disabled = true;
                        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...`;
                    }, 50);
                }
            }
        });
    });
});
</script>
</body>
</html>
