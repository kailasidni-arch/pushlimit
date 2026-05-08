</div><!-- end main-content -->

<script>
function toggleSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const btn = document.getElementById('hamburgerBtn');
  const overlay = document.getElementById('sidebarOverlay');
  sidebar.classList.toggle('open');
  btn.classList.toggle('active');
  overlay.classList.toggle('show');
}

function closeSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const btn = document.getElementById('hamburgerBtn');
  const overlay = document.getElementById('sidebarOverlay');
  sidebar.classList.remove('open');
  btn.classList.remove('active');
  overlay.classList.remove('show');
}

// Close sidebar when menu link clicked on mobile
document.querySelectorAll('.sidebar-menu a').forEach(link => {
  link.addEventListener('click', () => {
    if (window.innerWidth <= 900) closeSidebar();
  });
});
</script>

</body>
</html>
