<?php
$pageTitle = 'Messages';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireLogin();
require_once __DIR__ . '/../config/db.php';

// Dynamically load sidebar based on role
$userRole = $_SESSION['user_role'] ?? 'user';
if ($userRole === 'provider') {
    require_once __DIR__ . '/../includes/sidebar_provider.php';
} else {
    require_once __DIR__ . '/../includes/sidebar_user.php';
}

$myId = $_SESSION['user_id'];
$initialContactId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
?>
<div class="dashboard-main">
  <?php require_once __DIR__ . '/../includes/topbar.php'; ?>
  <div class="dashboard-content chat-page-wrapper">
    <div class="card shadow-sm border-0 bg-white overflow-hidden" style="height: calc(100vh - 140px); min-height: 500px; border-radius: var(--radius);">
      <div class="row g-0 h-100">
        
        <!-- Left Panel: Contacts List -->
        <div class="col-md-4 border-end d-flex flex-column h-100 bg-light bg-opacity-50">
          <div class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-chat-dots-fill text-primary me-2"></i>Recent Conversations</h6>
          </div>
          
          <!-- Contacts list container -->
          <div class="flex-grow-1 overflow-y-auto" id="contacts-list" style="max-height: 100%;">
            <div class="text-center py-5 d-none" id="contacts-empty">
              <i class="bi bi-chat-square-text text-muted opacity-50 display-6"></i>
              <p class="text-muted small mt-2">No active conversations.<br>Message a provider/user to start!</p>
            </div>
            <div id="contacts-loading" class="text-center py-4">
              <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            </div>
            <div class="list-group list-group-flush" id="contacts-group">
              <!-- Dynamically rendered contacts -->
            </div>
          </div>
        </div>

        <!-- Right Panel: Conversation Dialogue -->
        <div class="col-md-8 d-flex flex-column h-100 bg-white">
          <!-- Chat Window Header -->
          <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-white d-none" id="chat-header">
            <div class="d-flex align-items-center">
              <div class="chat-avatar me-3 text-white fw-bold rounded-circle d-flex align-items-center justify-content-center bg-primary" id="chat-header-avatar" style="width: 40px; height: 40px;">
                <!-- Initials -->
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark" id="chat-header-name"><!-- Contact Name --></h6>
                <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-0" id="chat-header-role" style="font-size: 0.7rem;"><!-- Role --></span>
              </div>
            </div>
          </div>

          <!-- Messages scroll container -->
          <div class="flex-grow-1 p-4 overflow-y-auto" id="messages-container" style="background-color: #f8f9fa;">
            <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center" id="chat-placeholder">
              <i class="bi bi-chat-left-heart text-primary opacity-25 display-2 mb-3"></i>
              <h5 class="fw-bold text-secondary">Your Inbox</h5>
              <p class="text-muted small px-4" style="max-width: 320px;">Select a conversation from the sidebar, or contact a user from their listings to start messaging in real-time.</p>
            </div>
            <div id="messages-list" class="d-flex flex-column gap-3">
              <!-- Dynamically loaded bubbles -->
            </div>
          </div>

          <!-- Bottom: Send box inputs -->
          <div class="p-3 border-top bg-white d-none" id="chat-input-area">
            <form id="chat-form" method="POST" autocomplete="off" class="d-flex gap-2">
              <input type="hidden" name="receiver_id" id="receiver-id">
              <input type="text" name="message" id="message-text" class="form-control bg-light border-0 py-2.5 px-3 rounded-pill" placeholder="Type a message..." required>
              <button type="submit" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; flex-shrink: 0;">
                <i class="bi bi-send-fill fs-6"></i>
              </button>
            </form>
          </div>

        </div>

      </div>
    </div>
  </div>
</div>

<style>
.chat-page-wrapper {
  padding-bottom: 0 !important;
}
.chat-avatar {
  text-transform: uppercase;
}
.chat-message-bubble {
  max-width: 70%;
  padding: 10px 16px;
  border-radius: 18px;
  font-size: 0.95rem;
  line-height: 1.4;
  word-wrap: break-word;
}
.chat-message-sent {
  align-self: flex-end;
  background-color: var(--primary);
  color: white;
  border-bottom-right-radius: 4px;
}
.chat-message-received {
  align-self: flex-start;
  background-color: #e9ecef;
  color: var(--text-dark);
  border-bottom-left-radius: 4px;
}
.chat-time {
  font-size: 0.7rem;
  opacity: 0.7;
  display: block;
  margin-top: 4px;
}
.chat-time-sent {
  text-align: right;
  color: #e8f0fe;
}
.chat-time-received {
  text-align: left;
  color: #6c757d;
}
.contact-item {
  transition: background-color 0.2s ease;
  cursor: pointer;
}
.contact-item:hover {
  background-color: #f1f3f5;
}
.contact-item.active-chat {
  background-color: #e8f0fe !important;
  border-left: 4px solid var(--primary);
}
.contact-item .contact-avatar {
  width: 44px;
  height: 44px;
  text-transform: uppercase;
  flex-shrink: 0;
}
.overflow-y-auto {
  overflow-y: auto !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let activeContactId = <?= $initialContactId ? $initialContactId : 'null' ?>;
    let pollInterval;
    let lastMessageCount = 0;

    const contactsGroup = document.getElementById('contacts-group');
    const contactsLoading = document.getElementById('contacts-loading');
    const contactsEmpty = document.getElementById('contacts-empty');
    const chatPlaceholder = document.getElementById('chat-placeholder');
    const chatHeader = document.getElementById('chat-header');
    const chatInputArea = document.getElementById('chat-input-area');
    const messagesContainer = document.getElementById('messages-container');
    const messagesList = document.getElementById('messages-list');
    const receiverIdInput = document.getElementById('receiver-id');
    const messageTextInput = document.getElementById('message-text');
    const chatForm = document.getElementById('chat-form');

    // Color mapper for initials avatars
    function getAvatarColor(name) {
        const colors = ['#1a73e8', '#28a745', '#fd7e14', '#6f42c1', '#17a2b8', '#dc3545', '#ffc107', '#4a9af5'];
        let sum = 0;
        for (let i = 0; i < name.length; i++) {
            sum += name.charCodeAt(i);
        }
        return colors[sum % colors.length];
    }

    // Convert date to readable timestamp
    function formatTime(sqlTimestamp) {
        const date = new Date(sqlTimestamp.replace(/-/g, '/'));
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // Refresh contact list on the sidebar
    function loadContacts(callback) {
        fetch('chat_handler.php?action=fetch_contacts')
            .then(res => res.json())
            .then(data => {
                contactsLoading.classList.add('d-none');
                
                if (data.status === 'success') {
                    let contacts = data.contacts;
                    
                    // If we have an initial contact from query param but they aren't in recent chats
                    const hasInitialInList = contacts.some(c => parseInt(c.contact_id) === activeContactId);
                    
                    if (activeContactId && !hasInitialInList) {
                        // Fetch the info of the external user and inject them temporarily
                        fetch(`chat_handler.php?action=get_contact_info&contact_id=${activeContactId}`)
                            .then(res => res.json())
                            .then(infoData => {
                                if (infoData.status === 'success') {
                                    const extContact = {
                                        contact_id: infoData.contact.contact_id,
                                        contact_name: infoData.contact.contact_name,
                                        contact_role: infoData.contact.contact_role,
                                        last_message: 'Tap to start conversation...',
                                        last_message_time: new Date().toISOString(),
                                        unread_count: 0
                                    };
                                    // Prepend to contacts array
                                    contacts.unshift(extContact);
                                    renderContacts(contacts);
                                    if (callback) callback();
                                }
                            });
                    } else {
                        renderContacts(contacts);
                        if (callback) callback();
                    }
                }
            })
            .catch(err => {
                console.error('Contacts load error:', err);
                contactsLoading.classList.add('d-none');
            });
    }

    // Renders the contact list into HTML
    function renderContacts(contacts) {
        if (contacts.length === 0) {
            contactsEmpty.classList.remove('d-none');
            contactsGroup.innerHTML = '';
            return;
        }
        contactsEmpty.classList.add('d-none');
        
        let html = '';
        contacts.forEach(c => {
            const initials = c.contact_name.charAt(0);
            const color = getAvatarColor(c.contact_name);
            const activeClass = parseInt(c.contact_id) === activeContactId ? 'active-chat' : '';
            const unreadBadgeHtml = parseInt(c.unread_count) > 0 ? `<span class="badge bg-danger rounded-pill ms-auto">${c.unread_count}</span>` : '';
            const msgSnippet = c.last_message ? (c.last_message.length > 30 ? c.last_message.substring(0, 30) + '...' : c.last_message) : '';
            
            html += `
                <div class="list-group-item list-group-item-action contact-item p-3 border-bottom ${activeClass}" data-id="${c.contact_id}" data-name="${c.contact_name}" data-role="${c.contact_role}">
                    <div class="d-flex align-items-center">
                        <div class="contact-avatar text-white fw-bold rounded-circle d-flex align-items-center justify-content-center me-3" style="background-color: ${color};">
                            ${initials}
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.95rem;">${c.contact_name}</h6>
                                <small class="text-muted" style="font-size: 0.75rem;">${c.last_message_time ? formatTime(c.last_message_time) : ''}</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <p class="text-muted small mb-0 text-truncate flex-grow-1">${msgSnippet}</p>
                                ${unreadBadgeHtml}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        contactsGroup.innerHTML = html;

        // Attach event listeners to newly rendered contacts list
        document.querySelectorAll('.contact-item').forEach(item => {
            item.addEventListener('click', function() {
                const id = parseInt(this.dataset.id);
                const name = this.dataset.name;
                const role = this.dataset.role;
                openChat(id, name, role);
            });
        });
    }

    // Opens conversation window
    function openChat(contactId, contactName, contactRole) {
        activeContactId = contactId;
        lastMessageCount = 0; // Reset message tracker
        
        // Highlight active contact visually in sidebar
        document.querySelectorAll('.contact-item').forEach(item => {
            if (parseInt(item.dataset.id) === contactId) {
                item.classList.add('active-chat');
                const badge = item.querySelector('.badge');
                if (badge) badge.remove(); // Remove unread count visually instantly
            } else {
                item.classList.remove('active-chat');
            }
        });

        // Configure Chat headers
        document.getElementById('chat-header-name').innerText = contactName;
        document.getElementById('chat-header-role').innerText = contactRole.charAt(0).toUpperCase() + contactRole.slice(1);
        const avatar = document.getElementById('chat-header-avatar');
        avatar.innerText = contactName.charAt(0);
        avatar.style.backgroundColor = getAvatarColor(contactName);

        receiverIdInput.value = contactId;

        // Toggle UI Panels
        chatPlaceholder.classList.add('d-none');
        chatHeader.classList.remove('d-none');
        chatInputArea.classList.remove('d-none');

        // Fetch messages log immediately and set polling interval
        fetchMessages();
        
        clearInterval(pollInterval);
        pollInterval = setInterval(fetchMessages, 3000);
    }

    // Retrieve messages from backend
    function fetchMessages() {
        if (!activeContactId) return;

        fetch(`chat_handler.php?action=fetch&contact_id=${activeContactId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const messages = data.messages;
                    
                    // Only re-render if message log length differs to avoid scroll jumps
                    if (messages.length !== lastMessageCount) {
                        renderMessages(messages);
                        lastMessageCount = messages.length;
                    }
                }
            })
            .catch(err => console.error('Fetch messages error:', err));
    }

    // Build bubble HTML feed
    function renderMessages(messages) {
        if (messages.length === 0) {
            messagesList.innerHTML = '<div class="text-center text-muted py-5 small italic">No messages yet. Send a greeting to start chatting!</div>';
            return;
        }

        let html = '';
        messages.forEach(msg => {
            const isSent = parseInt(msg.sender_id) === <?= $myId ?>;
            const bubbleClass = isSent ? 'chat-message-sent' : 'chat-message-received';
            const timeClass = isSent ? 'chat-time-sent' : 'chat-time-received';
            
            html += `
                <div class="chat-message-bubble ${bubbleClass}">
                    <span>${escapeHtml(msg.message)}</span>
                    <span class="chat-time ${timeClass}">${formatTime(msg.created_at)}</span>
                </div>
            `;
        });

        messagesList.innerHTML = html;
        scrollToBottom();
    }

    // Scroll chat window to bottom
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Simple HTML sanitizer
    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Form Send Message Submit
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = messageTextInput.value.trim();
        if (message === '' || !activeContactId) return;

        // Optimistically clean input
        messageTextInput.value = '';

        const formData = new FormData();
        formData.append('receiver_id', activeContactId);
        formData.append('message', message);

        fetch('chat_handler.php?action=send', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Immediately refresh chat feed and sidebar contacts
                fetchMessages();
                loadContacts();
            } else {
                alert('Message send failed: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Send message error:', err);
        });
    });

    // Initial page load
    loadContacts(function() {
        if (activeContactId) {
            // Locate contact information dynamically from rendered dataset if possible
            const item = document.querySelector(`.contact-item[data-id="${activeContactId}"]`);
            if (item) {
                openChat(activeContactId, item.dataset.name, item.dataset.role);
            } else {
                // Fallback details if they were newly generated by the initial contact syncer
                fetch(`chat_handler.php?action=get_contact_info&contact_id=${activeContactId}`)
                    .then(res => res.json())
                    .then(infoData => {
                        if (infoData.status === 'success') {
                            openChat(activeContactId, infoData.contact.contact_name, infoData.contact.contact_role);
                        }
                    });
            }
        }
    });

    // Cleanup interval if user leaves the tab
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(pollInterval);
        } else {
            if (activeContactId) {
                clearInterval(pollInterval);
                pollInterval = setInterval(fetchMessages, 3000);
            }
        }
    });
});
</script>
<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
