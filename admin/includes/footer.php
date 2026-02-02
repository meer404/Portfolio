            </main>
        </div>
    </div>
    
    <script>
        // Mobile sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebar-toggle');
        
        if (toggle) {
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 768 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.add('-translate-x-full');
            }
        });
    </script>
</body>
</html>
