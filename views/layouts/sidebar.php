<?php $ap = $page ?? 'dashboard'; ?>

<!-- ═══════════════════════════════
     SIDEBAR
═══════════════════════════════ -->
<aside class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sb-brand">
        <div class="sb-logo">
            <i class="fas fa-balance-scale"></i>
        </div>
        <div class="sb-brand-info">
            <span class="sb-title">نظام التعويضات</span>
            <span class="sb-subtitle">المديرية الإقليمية بمكناس</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sb-nav">

        <span class="sb-nav-label">القائمة الرئيسية</span>

        <a href="index.php" class="sb-link <?= $ap === 'dashboard' ? 'active' : '' ?>">
            <span class="sb-icon"><i class="fas fa-house"></i></span>
            <span class="sb-text">الرئيسية</span>
        </a>

        <a href="index.php?page=permanences" class="sb-link <?= $ap === 'permanences' ? 'active' : '' ?>">
            <span class="sb-icon"><i class="fas fa-clipboard-check"></i></span>
            <span class="sb-text">الديمومة</span>
        </a>

        <a href="index.php?page=heures_supp" class="sb-link <?= $ap === 'heures_supp' ? 'active' : '' ?>">
            <span class="sb-icon"><i class="fas fa-business-time"></i></span>
            <span class="sb-text">الساعات الإضافية</span>
        </a>

        <a href="index.php?page=rapports" class="sb-link <?= $ap === 'rapports' ? 'active' : '' ?>">
            <span class="sb-icon"><i class="fas fa-file-contract"></i></span>
            <span class="sb-text">التقارير</span>
        </a>

        <a href="index.php?page=observations" class="sb-link <?= $ap === 'observations' ? 'active' : '' ?>">
            <span class="sb-icon"><i class="fas fa-comment-dots"></i></span>
            <span class="sb-text">الملاحظات الإدارية</span>
        </a>

        <span class="sb-nav-label">التحليلات</span>

        <a href="index.php?page=statistiques" class="sb-link <?= $ap === 'statistiques' ? 'active' : '' ?>">
            <span class="sb-icon"><i class="fas fa-chart-line"></i></span>
            <span class="sb-text">الإحصائيات</span>
        </a>

        <a href="index.php?page=services" class="sb-link <?= $ap === 'services' ? 'active' : '' ?>">
            <span class="sb-icon"><i class="fas fa-landmark"></i></span>
            <span class="sb-text">إحصائيات المصالح</span>
        </a>

    </nav>

    <!-- User footer -->
    <div class="sb-user">
        <div class="sb-user-avatar">م</div>
        <div class="sb-user-info">
            <span class="sb-user-name">المسؤول</span>
            <span class="sb-user-role">مدير النظام</span>
        </div>
        <div class="sb-user-dot"></div>
    </div>

</aside>

<!-- ═══════════════════════════════
     GLOBAL TOP HEADER
═══════════════════════════════ -->
<header class="app-header">

    <div class="app-header-lead">
        <button class="sb-toggle" id="sbToggle" onclick="sbToggle()">
            <i class="fas fa-bars"></i>
        </button>
        <span class="app-brand-sm">نظام التعويضات</span>
    </div>

    <div class="app-header-right">
        <div class="app-header-date" id="appDate"></div>
        <div class="app-header-user">
            <div class="app-header-avatar">م</div>
            <span class="app-header-username">المسؤول</span>
        </div>
    </div>

</header>

<!-- Mobile overlay -->
<div class="sb-overlay" id="sbOverlay" onclick="sbClose()"></div>

<script>
function sbToggle() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sbOverlay').classList.toggle('active');
}

function sbClose() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sbOverlay').classList.remove('active');
}

(function () {
    const el   = document.getElementById('appDate');
    if (!el) return;
    const days   = ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
    const months = ['يناير','فبراير','مارس','أبريل','ماي','يونيو','يوليوز','غشت','شتنبر','أكتوبر','نونبر','دجنبر'];
    const n = new Date();
    el.textContent = days[n.getDay()] + ' ' + n.getDate() + ' ' + months[n.getMonth()] + ' ' + n.getFullYear();
})();
</script>
