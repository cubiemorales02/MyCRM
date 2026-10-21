<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$currentUserName = $_SESSION['fullname'];
$currentUserEmail = $_SESSION['email'];
$names = explode(" ", $currentUserName);
$userInitials = strtoupper($names[0][0] . $names[count($names)-1][0]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CRM Dashboard | <?php echo htmlspecialchars($currentUserName); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Inter', sans-serif; background: #f4f5fb; color: #1e293b; }
.dashboard { display: flex; height: 100vh; width: 100%; }
.sidebar {
    width: 240px;
    background: #1e293b;
    color: #fff;
    display: flex;
    flex-direction: column;
    padding-top: 20px;
}
.sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 1.4rem; }
.sidebar a {
    color: #fff;
    text-decoration: none;
    padding: 12px 24px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: 0.2s;
}
.sidebar a:hover { background: #3b82f6; border-radius: 8px; }
.main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
.topbar { background: white; padding: 16px 32px; display: flex; justify-content: flex-end; align-items: center; border-bottom: 1px solid #e9eef3; position: sticky; top: 0; z-index: 5; }
.profile-icon { position: relative; cursor: pointer; display: flex; align-items: center; justify-content: center; background: #f1f5f9; width: 48px; height: 48px; border-radius: 40px; transition: all 0.2s ease; border: 1px solid #e2e8f0; }
.profile-icon i { font-size: 1.6rem; color: #1e293b; }
.profile-dropdown { position: absolute; top: 62px; right: 32px; background: white; border-radius: 24px; width: 280px; box-shadow: 0 20px 35px -12px rgba(0,0,0,0.2); border: 1px solid #eef2ff; opacity: 0; visibility: hidden; transform: translateY(-12px); transition: all 0.2s; z-index: 100; }
.profile-dropdown.show { opacity: 1; visibility: visible; transform: translateY(0); }
.dropdown-header { padding: 20px 20px 12px; border-bottom: 1px solid #f0f2f5; display: flex; align-items: center; gap: 14px; }
.avatar-large { width: 52px; height: 52px; background: linear-gradient(145deg, #3b82f6, #6366f1); border-radius: 32px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.5rem; }
.user-info h4 { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
.user-info p { font-size: 0.75rem; color: #5b6e8c; }
.dropdown-actions { padding: 12px 12px; }
.dropdown-action-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-radius: 14px; margin: 4px 0; cursor: pointer; }
.dropdown-action-item i { width: 24px; color: #5b6e8c; }
.dropdown-action-item:hover { background: #f1f5f9; }
.logout-btn { border-top: 1px solid #edf2f7; margin-top: 6px; padding-top: 8px; color: #dc2626; }
.logout-btn i { color: #dc2626; }
.dashboard-content { padding: 32px; flex: 1; overflow-y: auto; }
.dashboard-content h1 { font-size: 2rem; margin-bottom: 16px; }
.cards { display: flex; gap: 20px; flex-wrap: wrap; }
.card { background: white; border-radius: 20px; padding: 24px; flex: 1; min-width: 220px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center; transition: 0.2s; cursor: pointer; }
.card i { font-size: 2rem; margin-bottom: 10px; color: #3b82f6; }
.card h4 { margin-bottom: 6px; font-size: 1rem; }
.card p { font-size: 0.85rem; color: #64748b; }
.card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.05); }
@media(max-width:768px){.dashboard{flex-direction:column;}.sidebar{width:100%;display:flex;flex-direction:row;overflow-x:auto;}.sidebar a{flex:1;text-align:center;}.main-content{flex:1;}}
</style>
</head>
<body>
<div class="dashboard">
    <!-- Sidebar for CRM Navigation -->
    <div class="sidebar">
        <h2>MyCRM</h2>
        <a href="#"><i class="fas fa-users"></i> Contacts</a>
        <a href="#"><i class="fas fa-tags"></i> Leads</a>
        <a href="#"><i class="fas fa-sticky-note"></i> Notes</a>
        <a href="#"><i class="fas fa-filter"></i> Filters</a>
        <a href="#"><i class="fas fa-chart-line"></i> Analytics</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div class="profile-icon" id="profileIconBtn"><i class="fas fa-user-circle"></i></div>
            <div class="profile-dropdown" id="profileDropdown">
                <div class="dropdown-header">
                    <div class="avatar-large" id="avatarInitials"><?php echo $userInitials; ?></div>
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($currentUserName); ?></h4>
                        <p><?php echo htmlspecialchars($currentUserEmail); ?></p>
                    </div>
                </div>
                <div class="dropdown-actions">
                    <div class="dropdown-action-item" id="viewProfileBtn"><i class="fas fa-id-card"></i> View profile</div>
                    <div class="dropdown-action-item" id="accountSettingsBtn"><i class="fas fa-cog"></i> Account settings</div>
                    <div class="dropdown-action-item logout-btn" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Log out</div>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
            <h1>Welcome, <?php echo explode(" ", $currentUserName)[0]; ?> 👋</h1>
            <div class="cards">
                <div class="card">
                    <i class="fas fa-user-plus"></i>
                    <h4>Create Contact</h4>
                    <p>Add a new contact to manage leads and customers</p>
                </div>
                <div class="card">
                    <i class="fas fa-dollar-sign"></i>
                    <h4>Assign Value</h4>
                    <p>Assign priority or value to your contacts</p>
                </div>
                <div class="card">
                    <i class="fas fa-sticky-note"></i>
                    <h4>Add Notes</h4>
                    <p>Write important notes about your contacts</p>
                </div>
                <div class="card">
                    <i class="fas fa-tags"></i>
                    <h4>Label Contacts</h4>
                    <p>Label contacts as lead, customer, or associate</p>
                </div>
                <div class="card">
                    <i class="fas fa-filter"></i>
                    <h4>Filter Contacts</h4>
                    <p>Filter contacts based on labels or priority</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Profile dropdown
document.getElementById('profileIconBtn').addEventListener('click', function(e){
    e.stopPropagation();
    document.getElementById('profileDropdown').classList.toggle('show');
});
document.addEventListener('click', function(e){
    if(!document.getElementById('profileDropdown').contains(e.target) && !document.getElementById('profileIconBtn').contains(e.target)){
        document.getElementById('profileDropdown').classList.remove('show');
    }
});
document.getElementById('logoutBtn').addEventListener('click', function(){
    window.location.href = "logout.php";
});
</script>
</body>
</html>
