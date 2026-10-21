<?php
session_start();
require_once 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$currentUserName = $_SESSION['fullname'];
$currentUserEmail = $_SESSION['email'];

// Create initials from full name
$nameParts = explode(' ', $currentUserName);
$userInitials = '';
foreach($nameParts as $part){
    $userInitials .= strtoupper($part[0]);
}
$userInitials = substr($userInitials, 0, 2);

// Get filter parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query based on filter
$query = "SELECT * FROM contacts WHERE user_id = ?";
$params = [$_SESSION['user_id']];
$types = "i";

if($filter !== 'all'){
    $query .= " AND status = ?";
    $params[] = $filter;
    $types .= "s";
}

if($search !== ''){
    $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$contacts = $stmt->get_result();

// Get counts for stats
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'lead' THEN 1 ELSE 0 END) as leads,
    SUM(CASE WHEN status = 'customer' THEN 1 ELSE 0 END) as customers,
    SUM(CASE WHEN status = 'associate' THEN 1 ELSE 0 END) as associates,
    SUM(contact_value) as total_value
    FROM contacts WHERE user_id = ?";
$stmt_stats = $conn->prepare($stats_query);
$stmt_stats->bind_param("i", $_SESSION['user_id']);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contacts | MyCRM</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Inter',sans-serif; background:#f8fafd; color:#0f172a; }

/* Sidebar styles */
.sidebar {
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100vh;
    background: white;
    border-right: 1px solid #eef2ff;
    transition: left 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    z-index: 1050;
    overflow-y: auto;
}
.sidebar.show { left: 0; }
.sidebar-header { padding: 28px 24px; border-bottom: 1px solid #eef2ff; }
.logo { font-size: 1.8rem; font-weight: 800; background: linear-gradient(135deg, #3b82f6, #8b5cf6); -webkit-background-clip: text; background-clip: text; color: transparent; display: flex; align-items: center; gap: 10px; }
.sidebar-nav { padding: 0 16px; }
.nav-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 18px;
    margin: 6px 0;
    border-radius: 16px;
    font-weight: 500;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s;
}
.nav-item:hover { background: #f1f5f9; transform: translateX(4px); }
.nav-item.active { background: #eff6ff; color: #2563eb; }
.nav-item i { width: 24px; }
.sidebar-footer { padding: 20px 16px; border-top: 1px solid #eef2ff; margin-top: auto; }
.logout-item { display: flex; align-items: center; gap: 14px; padding: 12px 18px; border-radius: 16px; color: #ef4444; cursor: pointer; }

.hamburger {
    position: fixed;
    top: 20px;
    left: 24px;
    width: 46px;
    height: 46px;
    background: white;
    border-radius: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 1100;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
    z-index: 1040;
}
.overlay.show { opacity: 1; visibility: visible; }

.main-content {
    flex: 1;
    width: 100%;
    transition: margin-left 0.35s;
    min-height: 100vh;
}
.topbar {
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(12px);
    padding: 16px 32px;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    border-bottom: 1px solid #eef2ff;
    position: sticky;
    top: 0;
    z-index: 1020;
}
.profile-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    cursor: pointer;
}
.content-area { padding: 32px 40px; max-width: 1400px; margin: 0 auto; }

/* Filter bar */
.filter-bar {
    background: white;
    border-radius: 20px;
    padding: 20px 24px;
    margin-bottom: 32px;
    border: 1px solid #eef2ff;
}
.filter-tabs {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.filter-btn {
    padding: 8px 20px;
    border-radius: 40px;
    background: #f1f5f9;
    color: #475569;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.2s;
}
.filter-btn.active {
    background: #3b82f6;
    color: white;
}
.filter-btn:hover:not(.active) { background: #e2e8f0; }
.search-box {
    display: flex;
    gap: 12px;
    align-items: center;
}
.search-box input {
    flex: 1;
    padding: 10px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 40px;
    font-family: 'Inter', sans-serif;
    font-size: 0.9rem;
}
.search-box button {
    padding: 10px 24px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 40px;
    cursor: pointer;
    font-weight: 500;
}

/* Stats cards */
.stats-mini {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
}
.stat-mini-card {
    background: white;
    padding: 16px 20px;
    border-radius: 20px;
    border: 1px solid #eef2ff;
}
.stat-mini-card h4 { font-size: 0.75rem; color: #64748b; margin-bottom: 8px; }
.stat-mini-card .number { font-size: 1.8rem; font-weight: 700; color: #0f172a; }

/* Contact cards */
.contacts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 24px;
}
.contact-card {
    background: white;
    border-radius: 24px;
    border: 1px solid #eef2ff;
    overflow: hidden;
    transition: all 0.2s;
}
.contact-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 30px -12px rgba(0,0,0,0.1);
}
.contact-header {
    padding: 20px 20px 12px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}
.contact-name h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 4px; }
.contact-name p { font-size: 0.75rem; color: #64748b; }
.contact-type-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
}
.badge-lead { background: #fef3c7; color: #92400e; }
.badge-customer { background: #d1fae5; color: #065f46; }
.badge-associate { background: #e0e7ff; color: #3730a3; }
.contact-details {
    padding: 12px 20px;
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
}
.detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    font-size: 0.85rem;
}
.detail-item i { width: 20px; color: #94a3b8; }
.value-badge {
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.85rem;
}
.contact-actions {
    padding: 12px 20px 20px;
    display: flex;
    gap: 12px;
}
.btn-icon {
    flex: 1;
    padding: 8px;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 40px;
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 500;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.btn-icon:hover { background: #f8fafc; border-color: #cbd5e1; }
.btn-primary { background: #3b82f6; color: white; border-color: #3b82f6; }
.btn-primary:hover { background: #2563eb; }

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}
.modal.show { display: flex; }
.modal-content {
    background: white;
    border-radius: 28px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    overflow-y: auto;
}
.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #eef2ff;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-body { padding: 24px; }
.modal-footer { padding: 16px 24px; border-top: 1px solid #eef2ff; display: flex; gap: 12px; justify-content: flex-end; }

.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.85rem; }
.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    font-family: 'Inter', sans-serif;
    font-size: 0.9rem;
}
.notes-list {
    max-height: 300px;
    overflow-y: auto;
}
.note-item {
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 8px;
}
.note-text { font-size: 0.85rem; margin-bottom: 6px; }
.note-date { font-size: 0.7rem; color: #94a3b8; }

@media (max-width: 768px) {
    .content-area { padding: 20px; }
    .contacts-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<div class="hamburger" id="hamburgerBtn"><i class="fas fa-bars"></i></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo"><i class="fas fa-chart-line"></i> MyCRM</div>
    </div>
    <div class="sidebar-nav">
        <div class="nav-item" onclick="location.href='../dashboard.php'"><i class="fas fa-home"></i> Dashboard</div>
        <div class="nav-item active"><i class="fas fa-address-book"></i> Contacts</div>
        <div class="nav-item" onclick="location.href='add_contact.php'"><i class="fas fa-user-plus"></i> Add Contact</div>
    </div>
    <div class="sidebar-footer">
        <div class="logout-item" onclick="location.href='../logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</div>
    </div>
</div>

<div class="overlay" id="overlay"></div>

<div class="main-content">
    <div class="topbar">
        <div class="profile-icon" id="profileIconBtn"><?php echo $userInitials; ?></div>
    </div>

    <div class="content-area">
        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-tabs">
                <a href="?filter=all" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">All Contacts</a>
                <a href="?filter=lead" class="filter-btn <?php echo $filter == 'lead' ? 'active' : ''; ?>">Leads</a>
                <a href="?filter=customer" class="filter-btn <?php echo $filter == 'customer' ? 'active' : ''; ?>">Customers</a>
                <a href="?filter=associate" class="filter-btn <?php echo $filter == 'associate' ? 'active' : ''; ?>">Associates</a>
            </div>
            <form method="GET" class="search-box">
                <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                <input type="text" name="search" placeholder="Search by name, email or phone..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
                <?php if($search): ?>
                    <a href="?filter=<?php echo $filter; ?>" class="filter-btn">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Stats -->
        <div class="stats-mini">
            <div class="stat-mini-card">
                <h4>Total Value</h4>
                <div class="number">$<?php echo number_format($stats['total_value'] ?? 0, 2); ?></div>
            </div>
            <div class="stat-mini-card">
                <h4>Total Contacts</h4>
                <div class="number"><?php echo $stats['total'] ?? 0; ?></div>
            </div>
            <div class="stat-mini-card">
                <h4>Leads</h4>
                <div class="number"><?php echo $stats['leads'] ?? 0; ?></div>
            </div>
            <div class="stat-mini-card">
                <h4>Customers</h4>
                <div class="number"><?php echo $stats['customers'] ?? 0; ?></div>
            </div>
        </div>

        <!-- Contacts Grid -->
        <div class="contacts-grid">
            <?php if($contacts->num_rows > 0): ?>
                <?php while($contact = $contacts->fetch_assoc()): ?>
                    <div class="contact-card" data-contact-id="<?php echo $contact['id']; ?>">
                        <div class="contact-header">
                            <div class="contact-name">
                                <h3><?php echo htmlspecialchars($contact['name']); ?></h3>
                                <p><?php echo htmlspecialchars($contact['email'] ?? 'No email'); ?></p>
                            </div>
                            <div class="contact-type-badge badge-<?php echo $contact['status']; ?>">
                                <?php echo ucfirst($contact['status']); ?>
                            </div>
                        </div>
                        <div class="contact-details">
                            <div class="detail-item">
                                <i class="fas fa-phone"></i>
                                <span><?php echo htmlspecialchars($contact['phone'] ?: 'No phone'); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-dollar-sign"></i>
                                <span>Value: </span>
                                <span class="value-badge">$<?php echo number_format($contact['contact_value'] ?? 0, 2); ?></span>
                            </div>
                        </div>
                        <div class="contact-actions">
                            <button class="btn-icon edit-status-btn" data-id="<?php echo $contact['id']; ?>" data-status="<?php echo $contact['status']; ?>">
                                <i class="fas fa-tag"></i> Edit Label
                            </button>
                            <button class="btn-icon edit-value-btn" data-id="<?php echo $contact['id']; ?>" data-value="<?php echo $contact['contact_value']; ?>">
                                <i class="fas fa-dollar-sign"></i> Set Value
                            </button>
                            <button class="btn-icon notes-btn" data-id="<?php echo $contact['id']; ?>">
                                <i class="fas fa-sticky-note"></i> Notes
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: white; border-radius: 24px;">
                    <i class="fas fa-users" style="font-size: 3rem; color: #cbd5e1;"></i>
                    <p style="margin-top: 16px;">No contacts found. <a href="add_contact.php">Add your first contact</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modals -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Change Contact Label</h3>
            <span style="cursor:pointer" onclick="closeModal('statusModal')">&times;</span>
        </div>
        <form id="statusForm">
            <div class="modal-body">
                <input type="hidden" id="statusContactId" name="contact_id">
                <div class="form-group">
                    <label>Contact Label</label>
                    <select name="status" id="contactStatusSelect" required>
                        <option value="lead">Lead</option>
                        <option value="customer">Customer</option>
                        <option value="associate">Associate</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-icon" onclick="closeModal('statusModal')">Cancel</button>
                <button type="submit" class="btn-icon btn-primary">Update Label</button>
            </div>
        </form>
    </div>
</div>

<div id="valueModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Set Contact Value</h3>
            <span style="cursor:pointer" onclick="closeModal('valueModal')">&times;</span>
        </div>
        <form id="valueForm">
            <div class="modal-body">
                <input type="hidden" id="valueContactId" name="contact_id">
                <div class="form-group">
                    <label>Value Amount ($)</label>
                    <input type="number" step="0.01" name="contact_value" id="contactValue" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-icon" onclick="closeModal('valueModal')">Cancel</button>
                <button type="submit" class="btn-icon btn-primary">Set Value</button>
            </div>
        </form>
    </div>
</div>

<div id="notesModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Contact Notes</h3>
            <span style="cursor:pointer" onclick="closeModal('notesModal')">&times;</span>
        </div>
        <div class="modal-body">
            <input type="hidden" id="notesContactId">
            <div class="form-group">
                <label>Add New Note</label>
                <textarea id="newNote" rows="3" placeholder="Write a note about this contact..."></textarea>
                <button type="button" class="btn-icon btn-primary" style="margin-top: 10px;" onclick="addNote()">Add Note</button>
            </div>
            <div class="form-group">
                <label>Previous Notes</label>
                <div id="notesList" class="notes-list"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-icon" onclick="closeModal('notesModal')">Close</button>
        </div>
    </div>
</div>

<script>
// Sidebar toggle
const hamburgerBtn = document.getElementById('hamburgerBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

hamburgerBtn.addEventListener('click', () => {
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
});

overlay.addEventListener('click', () => {
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
});

// Modal functions
function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

// Edit status buttons
document.querySelectorAll('.edit-status-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const contactId = btn.dataset.id;
        const currentStatus = btn.dataset.status;
        document.getElementById('statusContactId').value = contactId;
        document.getElementById('contactStatusSelect').value = currentStatus;
        document.getElementById('statusModal').classList.add('show');
    });
});

// Edit value buttons
document.querySelectorAll('.edit-value-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const contactId = btn.dataset.id;
        const currentValue = btn.dataset.value;
        document.getElementById('valueContactId').value = contactId;
        document.getElementById('contactValue').value = currentValue;
        document.getElementById('valueModal').classList.add('show');
    });
});

// Notes buttons
document.querySelectorAll('.notes-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const contactId = btn.dataset.id;
        document.getElementById('notesContactId').value = contactId;
        document.getElementById('notesModal').classList.add('show');
        await loadNotes(contactId);
    });
});

// Submit status form
document.getElementById('statusForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    const response = await fetch('update_contact_status.php', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    if(result.success) {
        location.reload();
    } else {
        alert('Error updating contact label');
    }
});

// Submit value form
document.getElementById('valueForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    const response = await fetch('update_contact_value.php', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    if(result.success) {
        location.reload();
    } else {
        alert('Error updating contact value');
    }
});

// Load notes
async function loadNotes(contactId) {
    const response = await fetch(`get_notes.php?contact_id=${contactId}`);
    const notes = await response.json();
    
    const notesList = document.getElementById('notesList');
    if(notes.length === 0) {
        notesList.innerHTML = '<p style="color: #94a3b8; text-align: center;">No notes yet</p>';
        return;
    }
    
    notesList.innerHTML = notes.map(note => `
        <div class="note-item">
            <div class="note-text">${escapeHtml(note.note_text)}</div>
            <div class="note-date">${new Date(note.created_at).toLocaleString()}</div>
        </div>
    `).join('');
}

// Add note
async function addNote() {
    const contactId = document.getElementById('notesContactId').value;
    const noteText = document.getElementById('newNote').value;
    
    if(!noteText.trim()) {
        alert('Please enter a note');
        return;
    }
    
    const formData = new FormData();
    formData.append('contact_id', contactId);
    formData.append('note_text', noteText);
    
    const response = await fetch('add_note.php', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    if(result.success) {
        document.getElementById('newNote').value = '';
        await loadNotes(contactId);
    } else {
        alert('Error adding note');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Profile dropdown (optional)
const profileIconBtn = document.getElementById('profileIconBtn');
if(profileIconBtn) {
    profileIconBtn.addEventListener('click', () => {
        alert('Profile: <?php echo addslashes($currentUserName); ?>');
    });
}
</script>
</body>
</html>