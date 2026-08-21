<!-- Footer Component -->
<footer class="site-footer">
    <div class="footer-container">
        <!-- Col 1: Store Intro -->
        <div class="footer-col">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1rem;">
                <div class="brand-icon" style="width: 40px; height: 40px; font-size: 20px;">
                    <i class="fa-solid fa-paw"></i>
                </div>
                <span style="font-size: 20px; font-weight: 900; color: #FFFFFF; letter-spacing: -0.5px;">Mật Ngọt Bear</span>
            </div>
            <p>Thương hiệu gấu bông cao cấp hàng đầu Việt Nam. Chúng mình mang đến những người bạn nhồi bông mềm mại, êm ái, an toàn 100% cho làn da và chất lượng thêu tỉ mỉ chuẩn từng đường kim mũi chỉ.</p>
            <p><i class="fa-solid fa-location-dot" style="color: var(--honey-gold); margin-right: 8px;"></i> Showroom: 123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội</p>
            <p><i class="fa-solid fa-phone" style="color: var(--honey-gold); margin-right: 8px;"></i> Hotline tư vấn & đặt hàng: <strong>097.989.6616</strong></p>
        </div>

        <!-- Col 2: Categories -->
        <div class="footer-col">
            <h4>BỘ SƯU TẬP TEDDY</h4>
            <ul class="footer-links" id="footer-categories-list">
                <li><a href="{{ route('products.index', ['category_id' => 1]) }}"><i class="fa-solid fa-angle-right"></i> Teddy Classic Cổ Điển</a></li>
                <li><a href="{{ route('products.index', ['category_id' => 2]) }}"><i class="fa-solid fa-angle-right"></i> Butter Bear Siêu Hot</a></li>
                <li><a href="{{ route('products.index', ['category_id' => 3]) }}"><i class="fa-solid fa-angle-right"></i> Teddy Mr. Bean Vintage</a></li>
                <li><a href="{{ route('products.index', ['category_id' => 4]) }}"><i class="fa-solid fa-angle-right"></i> Teddy Couple Đôi Bạn</a></li>
                <li><a href="{{ route('products.index', ['category_id' => 5]) }}"><i class="fa-solid fa-angle-right"></i> Gối Bông Teddy Đa Năng</a></li>
            </ul>
        </div>

        <!-- Col 3: Customer Service (Chờ đường dẫn của bạn nhóm) -->
        <div class="footer-col">
            <h4>CHÍNH SÁCH BÁN HÀNG</h4>
            <ul class="footer-links">
                <li><a href="#"><i class="fa-solid fa-angle-right"></i> Đổi Trả Trong 7 Ngày</a></li>
                <li><a href="#"><i class="fa-solid fa-angle-right"></i> Giao Hàng Toàn Quốc 30k</a></li>
                <li><a href="#"><i class="fa-solid fa-angle-right"></i> Bảo Hành Đường May Trọn Đời</a></li>
                <li><a href="#"><i class="fa-solid fa-angle-right"></i> Gói Quà & Tặng Thiệp Xinh</a></li>
            </ul>
        </div>

        <!-- Col 4: Fanpage & Admin portal -->
        <div class="footer-col">
            <h4>KẾT NỐI VỚI CHÚNG MÌNH</h4>
            <p>Theo dõi fanpage Mật Ngọt Bear để nhận voucher giảm giá 15% cho đơn hàng đầu tiên!</p>
            <div style="display: flex; gap: 10px;">
                <a href="https://facebook.com" target="_blank" style="width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: #FFFFFF;"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="https://tiktok.com" target="_blank" style="width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: #FFFFFF;"><i class="fa-brands fa-tiktok"></i></a>
                <a href="https://instagram.com" target="_blank" style="width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: #FFFFFF;"><i class="fa-brands fa-instagram"></i></a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div>&copy; 2026 Mật Ngọt Bear. Bản quyền thuộc về Mật Ngọt Bear - Thế giới gấu bông mềm mịn.</div>
        <div style="display: flex; gap: 16px;">
            <span><i class="fa-solid fa-shield-halved" style="color: var(--honey-gold);"></i> 100% Bông Sạch Kháng Khuẩn</span>
            <span><i class="fa-solid fa-truck-fast" style="color: var(--honey-gold);"></i> Đóng Gói Hút Chân Không Gọn Gàng</span>
        </div>
    </div>
</footer>
