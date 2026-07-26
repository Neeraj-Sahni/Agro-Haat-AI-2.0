<footer class="footer mt-auto py-5 glass-card border-0 rounded-0 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="fw-bold mb-4 text-success">Agro-Haat</h5>
                <p class="text-muted small">Empowering farmers with AI-driven insights and connecting them directly to customers for a fresher tomorrow.</p>
                <div class="d-flex gap-3 mt-4">
                    <a href="#" class="text-success"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-success"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-success"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-success"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <h6 class="fw-bold mb-4">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted">About Us</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Our Farmers</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Marketplace</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Careers</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-4">
                <h6 class="fw-bold mb-4">Support</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Help Center</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Safety Center</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Community</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-4">
                <h6 class="fw-bold mb-4">Subscribe to Newsletter</h6>
                <div class="input-group mb-3 glass-card p-1">
                    <input type="text" class="form-control border-0 bg-transparent" placeholder="Email Address">
                    <button class="btn btn-premium btn-premium-green rounded-pill px-4" type="button">Join</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer Bottom -->
    <div class="footer-bottom py-3">
        <div class="container text-center text-muted small">
            &copy; <?= date('Y') ?> Agro-Haat. All rights reserved. | <a href="#" class="text-decoration-none text-muted">Privacy Policy</a>
        </div>
    </div>
</footer>

<!-- Chatbot -->
<?php if (isset($_SESSION['user_id'])): ?>
    <?php include __DIR__ . '/chatbot.php'; ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
