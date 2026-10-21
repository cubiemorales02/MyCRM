<?php
session_start();
require_once 'db.php';

// Redirect to login if user is not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
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

// Get real contact statistics
$stats_query = "SELECT 
    COUNT(*) as total_contacts,
    SUM(CASE WHEN status = 'lead' THEN 1 ELSE 0 END) as total_leads,
    SUM(CASE WHEN status = 'customer' THEN 1 ELSE 0 END) as total_customers,
    SUM(CASE WHEN status = 'associate' THEN 1 ELSE 0 END) as total_associates,
    SUM(contact_value) as total_value,
    COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as new_contacts_30d
    FROM contacts 
    WHERE user_id = ?";
$stmt_stats = $conn->prepare($stats_query);
$stmt_stats->bind_param("i", $_SESSION['user_id']);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();

$total_contacts = $stats['total_contacts'] ?? 0;
$total_leads = $stats['total_leads'] ?? 0;
$total_customers = $stats['total_customers'] ?? 0;
$total_associates = $stats['total_associates'] ?? 0;
$total_value = $stats['total_value'] ?? 0;
$new_contacts = $stats['new_contacts_30d'] ?? 0;

// Get recent contacts (limit to 5)
$recent_query = "SELECT id, name, email, phone, status, contact_value, created_at 
                 FROM contacts 
                 WHERE user_id = ? 
                 ORDER BY created_at DESC 
                 LIMIT 5";
$stmt_recent = $conn->prepare($recent_query);
$stmt_recent->bind_param("i", $_SESSION['user_id']);
$stmt_recent->execute();
$recent_contacts = $stmt_recent->get_result();

// Check if user has any contacts
$has_contacts = $total_contacts > 0;

// Function to format Philippine Peso
function formatPHP($amount) {
    return '₱' . number_format($amount, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<title>Dashboard | MyCRM</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: #f8fafd;
    color: #0f172a;
    overflow-x: hidden;
}

/* Modern scrollbar */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Dashboard container */
.dashboard {
    display: flex;
    min-height: 100vh;
    position: relative;
}

/* ========== SIDEBAR MODERN ========== */
.sidebar {
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100vh;
    background: linear-gradient(180deg, #ffffff 0%, #fefefe 100%);
    backdrop-filter: blur(0px);
    border-right: 1px solid rgba(203, 213, 225, 0.4);
    box-shadow: 8px 0 24px rgba(0, 0, 0, 0.03);
    transition: left 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    z-index: 1050;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.sidebar.show {
    left: 0;
}

.sidebar-header {
    padding: 28px 24px;
    border-bottom: 1px solid #eef2ff;
    margin-bottom: 24px;
}

.logo {
    font-size: 1.8rem;
    font-weight: 800;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo i {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    font-size: 1.6rem;
}

.sidebar-nav {
    flex: 1;
    padding: 0 16px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 18px;
    margin: 6px 0;
    border-radius: 16px;
    font-weight: 500;
    font-size: 0.95rem;
    color: #334155;
    transition: all 0.2s ease;
    cursor: pointer;
}

.nav-item i {
    width: 24px;
    font-size: 1.2rem;
    color: #64748b;
    transition: all 0.2s;
}

.nav-item:hover {
    background: #f1f5f9;
    color: #1e293b;
    transform: translateX(4px);
}

.nav-item:hover i {
    color: #3b82f6;
}

.nav-item.active {
    background: linear-gradient(95deg, #eff6ff, #ffffff);
    color: #2563eb;
    font-weight: 600;
    border-right: 3px solid #3b82f6;
}

.nav-item.active i {
    color: #3b82f6;
}

.sidebar-footer {
    padding: 20px 16px 30px;
    border-top: 1px solid #eef2ff;
    margin-top: auto;
}

.logout-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 18px;
    border-radius: 16px;
    color: #ef4444;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s;
}

.logout-item i {
    color: #ef4444;
    width: 24px;
}

.logout-item:hover {
    background: #fef2f2;
    transform: translateX(4px);
}

/* Hamburger button modern */
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
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03);
    transition: all 0.2s;
    border: 1px solid #eef2ff;
}

.hamburger i {
    font-size: 1.3rem;
    color: #3b82f6;
}

.hamburger:hover {
    background: #f8fafc;
    transform: scale(0.96);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

/* Overlay */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(3px);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 1040;
}

.overlay.show {
    opacity: 1;
    visibility: visible;
}

/* Main content */
.main-content {
    flex: 1;
    width: 100%;
    transition: margin-left 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    min-height: 100vh;
    background: #f8fafd;
}

/* Topbar premium */
.topbar {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px);
    padding: 16px 32px;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    border-bottom: 1px solid rgba(203, 213, 225, 0.5);
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
    font-size: 1.1rem;
    color: white;
    cursor: pointer;
    box-shadow: 0 6px 14px rgba(59, 130, 246, 0.25);
    transition: all 0.2s ease;
    border: 2px solid white;
}

.profile-icon:hover {
    transform: scale(1.03);
    box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
}

/* Dropdown card */
.profile-dropdown {
    position: absolute;
    top: 78px;
    right: 32px;
    background: white;
    border-radius: 28px;
    width: 320px;
    box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.2);
    border: 1px solid #eef2ff;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px) scale(0.96);
    transition: all 0.2s ease;
    z-index: 1030;
    backdrop-filter: blur(0px);
}

.profile-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

.dropdown-header {
    padding: 22px 24px 18px;
    border-bottom: 1px solid #f0f2f5;
    display: flex;
    align-items: center;
    gap: 18px;
}

.avatar-large {
    width: 64px;
    height: 64px;
    background: linear-gradient(145deg, #3b82f6, #8b5cf6);
    border-radius: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.7rem;
    box-shadow: 0 10px 15px -6px rgba(59, 130, 246, 0.3);
}

.user-info h4 {
    font-size: 1.05rem;
    font-weight: 700;
    margin-bottom: 6px;
    color: #0f172a;
}

.user-info p {
    font-size: 0.75rem;
    color: #5b6e8c;
    letter-spacing: 0.2px;
}

.dropdown-actions {
    padding: 12px 16px 20px;
}

.dropdown-action-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 20px;
    border-radius: 20px;
    margin: 4px 0;
    transition: 0.15s;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    color: #1f2a44;
}

.dropdown-action-item i {
    width: 24px;
    color: #5b6e8c;
    font-size: 1.1rem;
}

.dropdown-action-item:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}

/* Dashboard content area modern */
.dashboard-content {
    padding: 36px 40px;
    max-width: 1400px;
    margin: 0 auto;
}

.welcome-card {
    background: linear-gradient(120deg, #ffffff 0%, #fefefe 100%);
    border-radius: 36px;
    padding: 36px 40px;
    border: 1px solid rgba(203, 213, 225, 0.4);
    box-shadow: 0 12px 30px -12px rgba(0, 0, 0, 0.05);
    margin-bottom: 32px;
    transition: all 0.2s;
}

.welcome-card h1 {
    font-size: 2.2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #0f172a, #334155);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin-bottom: 12px;
    letter-spacing: -0.3px;
}

.welcome-card p {
    color: #475569;
    font-size: 1rem;
    line-height: 1.5;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.welcome-card i {
    color: #3b82f6;
    font-size: 1rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.stat-card {
    background: white;
    border-radius: 28px;
    padding: 24px 20px;
    border: 1px solid #eef2ff;
    transition: 0.25s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 30px -15px rgba(0, 0, 0, 0.08);
    border-color: #e0e7ff;
}

.stat-icon {
    width: 52px;
    height: 52px;
    background: #eff6ff;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.stat-icon i {
    font-size: 1.8rem;
    color: #3b82f6;
}

.stat-card h3 {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    color: #5b6e8c;
    margin-bottom: 10px;
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 6px;
}

.stat-trend {
    font-size: 0.75rem;
    color: #10b981;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Empty State */
.empty-state {
    background: white;
    border-radius: 28px;
    padding: 60px 40px;
    text-align: center;
    border: 1px solid #eef2ff;
    margin-bottom: 32px;
}

.empty-icon {
    font-size: 5rem;
    color: #cbd5e1;
    margin-bottom: 24px;
}

.empty-state h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 12px;
    color: #0f172a;
}

.empty-state p {
    color: #64748b;
    margin-bottom: 32px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.empty-state .btn-primary {
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    color: white;
    border: none;
    padding: 12px 32px;
    border-radius: 40px;
    font-weight: 600;
    cursor: pointer;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s;
}

.empty-state .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -10px rgba(59, 130, 246, 0.4);
}

/* Recent Contacts Section */
.recent-section {
    background: white;
    border-radius: 28px;
    padding: 32px;
    border: 1px solid #eef2ff;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.section-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #1e293b;
}

.view-all-link {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.view-all-link:hover {
    text-decoration: underline;
}

.contacts-table {
    width: 100%;
    overflow-x: auto;
}

.contacts-table table {
    width: 100%;
    border-collapse: collapse;
}

.contacts-table th {
    text-align: left;
    padding: 12px 8px;
    color: #64748b;
    font-weight: 500;
    font-size: 0.8rem;
    border-bottom: 1px solid #eef2ff;
}

.contacts-table td {
    padding: 14px 8px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.9rem;
}

.contacts-table tr:hover {
    background: #f8fafc;
}

.contact-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
}

.badge-lead {
    background: #fef3c7;
    color: #92400e;
}

.badge-customer {
    background: #d1fae5;
    color: #065f46;
}

.badge-associate {
    background: #e0e7ff;
    color: #3730a3;
}

.value-cell {
    font-weight: 600;
    color: #0f172a;
}

/* Quick actions */
.quick-actions {
    margin-top: 32px;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.action-btn {
    background: white;
    border: 1px solid #e2e8f0;
    padding: 14px 28px;
    border-radius: 60px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: 0.2s;
    font-size: 0.9rem;
    color: #1f2a44;
}

.action-btn i {
    font-size: 1rem;
    color: #3b82f6;
}

.action-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px -8px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .dashboard-content {
        padding: 20px;
    }
    .welcome-card {
        padding: 24px 20px;
    }
    .welcome-card h1 {
        font-size: 1.6rem;
    }
    .topbar {
        padding: 12px 20px;
    }
    .profile-dropdown {
        right: 16px;
        width: 290px;
    }
    .hamburger {
        left: 16px;
        top: 16px;
        width: 42px;
        height: 42px;
    }
    .contacts-table {
        font-size: 0.8rem;
    }
    .contacts-table td, .contacts-table th {
        padding: 8px 4px;
    }
}
</style>
</head>
<body>
<div class="dashboard">
    <!-- Hamburger -->
    <div class="hamburger" id="hamburgerBtn">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar Modern -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-chart-line"></i> 
                <span>MyCRM</span>
            </div>
        </div>
        <div class="sidebar-nav">
            <div class="nav-item active" data-link="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </div>
            <div class="nav-item" data-link="contacts.php">
                <i class="fas fa-address-book"></i> Contacts
            </div>
            <div class="nav-item" data-link="add_contact.php">
                <i class="fas fa-user-plus"></i> Add Contact
            </div>
        </div>
        <div class="sidebar-footer">
            <div class="logout-item" id="logoutBtn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </div>
        </div>
    </div>

    <div class="overlay" id="overlay"></div>

    <div class="main-content" id="mainContent">
        <div class="topbar">
            <div class="profile-icon" id="profileIconBtn"><?php echo $userInitials; ?></div>
            <div class="profile-dropdown" id="profileDropdown">
                <div class="dropdown-header">
                    <div class="avatar-large"><?php echo $userInitials; ?></div>
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($currentUserName); ?></h4>
                        <p><?php echo htmlspecialchars($currentUserEmail); ?></p>
                    </div>
                </div>
                <div class="dropdown-actions">
                    <div class="dropdown-action-item" id="viewProfileBtn"><i class="fas fa-id-card"></i> View profile</div>
                    <div class="dropdown-action-item" id="accountSettingsBtn"><i class="fas fa-sliders-h"></i> Account settings</div>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="welcome-card">
                <h1>Welcome back, <?php echo explode(' ', $currentUserName)[0]; ?> 👋</h1>
                <p><i class="fas fa-chart-simple"></i> Your CRM dashboard is ready. Manage contacts, track activity and grow relationships.</p>
            </div>

            <?php if(!$has_contacts): ?>
                <!-- Empty State - No Contacts Yet -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>No contacts yet</h3>
                    <p>Start building your CRM by adding your first contact. You can track leads, customers, and associates all in one place.</p>
                    <button class="btn-primary" id="emptyStateAddContact">
                        <i class="fas fa-plus-circle"></i> Add Your First Contact
                    </button>
                </div>
            <?php else: ?>
                <!-- Statistics Grid - Only show if contacts exist -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <h3>Total Contacts</h3>
                        <div class="stat-number"><?php echo $total_contacts; ?></div>
                        <?php if($new_contacts > 0): ?>
                        <div class="stat-trend"><i class="fas fa-arrow-up"></i> +<?php echo $new_contacts; ?> this month</div>
                        <?php endif; ?>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-tag"></i></div>
                        <h3>Leads</h3>
                        <div class="stat-number"><?php echo $total_leads; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                        <h3>Customers</h3>
                        <div class="stat-number"><?php echo $total_customers; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-handshake"></i></div>
                        <h3>Associates</h3>
                        <div class="stat-number"><?php echo $total_associates; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-peso-sign"></i></div>
                        <h3>Total Value</h3>
                        <div class="stat-number"><?php echo formatPHP($total_value); ?></div>
                    </div>
                </div>

                <!-- Recent Contacts Section -->
                <div class="recent-section">
                    <div class="section-header">
                        <h3 class="section-title">Recent Contacts</h3>
                        <a href="contacts.php" class="view-all-link">
                            View all contacts <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <?php if($recent_contacts->num_rows > 0): ?>
                        <div class="contacts-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Label</th>
                                        <th>Value</th>
                                        <th>Added</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($contact = $recent_contacts->fetch_assoc()): ?>
                                    <tr onclick="window.location.href='contacts.php'" style="cursor:pointer">
                                        <td><strong><?php echo htmlspecialchars($contact['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($contact['email'] ?: '—'); ?></td>
                                        <td><?php echo htmlspecialchars($contact['phone'] ?: '—'); ?></td>
                                        <td>
                                            <span class="contact-badge badge-<?php echo $contact['status']; ?>">
                                                <?php echo ucfirst($contact['status']); ?>
                                            </span>
                                        </td>
                                        <td class="value-cell"><?php echo formatPHP($contact['contact_value']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($contact['created_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; color: #64748b; padding: 40px;">No recent contacts found.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Quick Actions - Always visible -->
            <div class="quick-actions">
                <div class="section-title">Quick actions</div>
                <div class="action-buttons">
                    <div class="action-btn" id="goToContacts">
                        <i class="fas fa-address-book"></i> Browse contacts
                    </div>
                    <div class="action-btn" id="goToAddContact">
                        <i class="fas fa-plus-circle"></i> New contact
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Sidebar toggle
const hamburgerBtn = document.getElementById('hamburgerBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const mainContent = document.getElementById('mainContent');

function closeSidebar() {
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
}

function openSidebar() {
    sidebar.classList.add('show');
    overlay.classList.add('show');
}

hamburgerBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    if (sidebar.classList.contains('show')) {
        closeSidebar();
    } else {
        openSidebar();
    }
});

overlay.addEventListener('click', () => {
    closeSidebar();
});

// Navigation items
const navItems = document.querySelectorAll('.nav-item');
navItems.forEach(item => {
    item.addEventListener('click', (e) => {
        const link = item.getAttribute('data-link');
        if(link) {
            window.location.href = link;
        }
    });
});

// Logout
const logoutBtn = document.getElementById('logoutBtn');
if(logoutBtn) {
    logoutBtn.addEventListener('click', () => {
        window.location.href = 'logout.php';
    });
}

// Profile dropdown
const profileDropdown = document.getElementById('profileDropdown');
const profileIconBtn = document.getElementById('profileIconBtn');
const viewProfileBtn = document.getElementById('viewProfileBtn');
const accountSettingsBtn = document.getElementById('accountSettingsBtn');

profileIconBtn.addEventListener('click', (e)=>{
    e.stopPropagation();
    profileDropdown.classList.toggle('show');
});

document.addEventListener('click', (e)=>{
    if(!profileIconBtn.contains(e.target) && !profileDropdown.contains(e.target)){
        profileDropdown.classList.remove('show');
    }
});

viewProfileBtn.addEventListener('click', (e)=>{
    e.stopPropagation();
    alert(`Name: <?php echo addslashes($currentUserName); ?>\nEmail: <?php echo addslashes($currentUserEmail); ?>`);
    profileDropdown.classList.remove('show');
});

accountSettingsBtn.addEventListener('click', (e)=>{
    e.stopPropagation();
    alert("Account settings - coming soon");
    profileDropdown.classList.remove('show');
});

// Quick actions buttons
const goToContacts = document.getElementById('goToContacts');
const goToAddContact = document.getElementById('goToAddContact');
const emptyStateAddContact = document.getElementById('emptyStateAddContact');

if(goToContacts) goToContacts.addEventListener('click', () => { window.location.href = 'contacts.php'; });
if(goToAddContact) goToAddContact.addEventListener('click', () => { window.location.href = 'add_contact.php'; });
if(emptyStateAddContact) emptyStateAddContact.addEventListener('click', () => { window.location.href = 'add_contact.php'; });

// Prevent sidebar closing when clicking inside
sidebar.addEventListener('click', (e) => {
    e.stopPropagation();
});
</script>
</body>
</html>