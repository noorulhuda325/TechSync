            </div> <!-- End content-wrapper -->
        </main> <!-- End main-content -->
    </div> <!-- End app-wrapper -->
    
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    <?php if (isset($additional_scripts)): ?>
        <?php foreach ($additional_scripts as $script): ?>
            <script src="<?php echo BASE_URL . $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

