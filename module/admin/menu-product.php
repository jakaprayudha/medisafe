<ul class="nav nav-tabs" id="myTab" role="tablist">
   <li class="nav-item" role="presentation">
      <a href="module/admin/product_details">
         <button class="nav-link active" id="office-tab" type="button" role="tab" aria-controls="office-tab-pane" aria-selected="true">Basic</button>
      </a>
   </li>
   <li class="nav-item" role="presentation">
      <a href="module/admin/product_advanced">
         <button class="nav-link" id="sales-tab" type="button" role="tab" aria-controls="sales-tab-pane" aria-selected="false">Advanced Data</button>
      </a>
   </li>
   <li class="nav-item" role="presentation">
      <a href="module/admin/product_price">
         <button class="nav-link" id="sales-tab" type="button" role="tab" aria-controls="sales-tab-pane" aria-selected="false">Harga Jual Lainnya</button>
      </a>
   </li>
   <li class="nav-item" role="presentation">
      <a href="module/admin/product_diskon">
         <button class="nav-link" id="sales-tab" type="button" role="tab" aria-controls="sales-tab-pane" aria-selected="false">Diskon</button>
      </a>
   </li>
   <li class="nav-item" role="presentation">
      <a href="module/admin/product_image">
         <button class="nav-link" id="sales-tab" type="button" role="tab" aria-controls="sales-tab-pane" aria-selected="false">Foto Product</button>
      </a>
   </li>
</ul>

<script>
   document.addEventListener("DOMContentLoaded", function() {
      const currentPath = window.location.pathname;

      document.querySelectorAll('.nav-tabs a').forEach(link => {
         const href = link.getAttribute('href');

         if (currentPath.includes(href)) {
            // Hapus class active dari semua tab
            document.querySelectorAll('.nav-tabs .nav-link').forEach(btn => btn.classList.remove('active'));

            // Tambahkan class active ke tombol dalam <a> yang cocok
            link.querySelector('.nav-link').classList.add('active');
         }
      });
   });
</script>