document.addEventListener('DOMContentLoaded', function () {

    /* ── Upload: show filename ── */
    document.querySelectorAll('.upload-card input[type="file"]').forEach(input => {
        input.addEventListener('change', function () {
            const file  = this.files[0];
            const card  = this.closest('.upload-card');
            const title = card?.querySelector('.upload-title');
            const desc  = card?.querySelector('.upload-desc');
            if (file && title) {
                title.textContent = '✅ ' + file.name;
                if (desc) desc.textContent = 'اضغط على زر الاستيراد للمتابعة';
                card.style.borderColor = '#10b981';
                card.style.background  = 'linear-gradient(135deg,#ecfdf5,#d1fae5)';
            }
        });
    });

    /* ── Delete confirmation ── */
    document.querySelectorAll('.btn-delete[type="submit"]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (!confirm('هل أنت متأكد من هذا الإجراء؟')) e.preventDefault();
        });
    });

    /* ── Auto-dismiss success alerts ── */
    const successBox = document.querySelector('.success');
    if (successBox) {
        setTimeout(() => {
            successBox.style.transition = 'opacity .5s ease, transform .5s ease';
            successBox.style.opacity    = '0';
            successBox.style.transform  = 'translateY(-8px)';
            setTimeout(() => successBox.remove(), 520);
        }, 3800);
    }

    /* ── Animate cards on load ── */
    document.querySelectorAll('.card, .box, .service-card').forEach((el, i) => {
        el.style.animationDelay = (i * 0.04) + 's';
        el.classList.add('animate-in');
    });

    /* ── Table row click ripple ── */
    document.querySelectorAll('tbody tr').forEach(row => {
        row.style.cursor = row.classList.contains('liste-row') ? 'pointer' : '';
    });

});

/* ── inject animation style once ── */
(function () {
    const s = document.createElement('style');
    s.textContent = `
      .animate-in {
        animation: slideUp .35s cubic-bezier(.4,0,.2,1) both;
      }
      @keyframes slideUp {
        from { opacity:0; transform:translateY(14px); }
        to   { opacity:1; transform:translateY(0); }
      }
    `;
    document.head.appendChild(s);
})();
