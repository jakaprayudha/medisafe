<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<nav>
   <div class="nav nav-tabs" id="nav-tab" role="tablist">
      <a class="nav-link <?= ($current == 'farmasi_order.php' ? 'active' : '') ?>"
         href="module/admin/farmasi_order"
         role="tab">
         <iconify-icon icon="covid:vaccine-protection-medicine-pill"></iconify-icon> Resep Dokter
      </a>

      <a class="nav-link <?= ($current == 'farmasi_order_luar.php' ? 'active' : '') ?>"
         href="module/admin/farmasi_order_luar"
         role="tab">
         <iconify-icon icon="solar:call-medicine-rounded-broken"></iconify-icon> Resep Luar
      </a>
      <a class="nav-link <?= ($current == 'farmasi_pembelian.php' ? 'active' : '') ?>"
         href="module/admin/farmasi_pembelian"
         role="tab">
         <iconify-icon icon="lucide:file-box"></iconify-icon> Pembelian Farmasi
      </a>
      <a class="nav-link <?= ($current == 'farmasi_stock.php' ? 'active' : '') ?>"
         href="module/admin/farmasi_stock"
         role="tab">
         <iconify-icon icon="vaadin:stock"></iconify-icon> Persediaan (Stock)
      </a>
   </div>
</nav>