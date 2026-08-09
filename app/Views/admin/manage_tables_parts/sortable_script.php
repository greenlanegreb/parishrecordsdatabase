<?php
declare(strict_types=1);
?>
<!-- AJAX Sortable Initialization Script -->
<?php if (!empty($columns)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tbody = document.getElementById('sortable-columns-body');
    if (tbody) {
        Sortable.create(tbody, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function () {
                var rows = tbody.querySelectorAll('tr[data-column-id]');
                var sortOrders = {};
              
                rows.forEach(function (row, index) {
                    var colId = row.getAttribute('data-column-id');
                    sortOrders[colId] = index + 1;
                });

                var formData = new URLSearchParams();
                formData.append('action', 'update_order_batch');
                formData.append('table_id', '<?= $activeTableId ?>');
                formData.append('csrf_token', '<?= generate_csrf_token() ?>');
              
                for (var colId in sortOrders) {
                    formData.append('sort_orders[' + colId + ']', sortOrders[colId]);
                }

                fetch('<?= $basePath ?>/admin/tables', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        console.error('Failed to sync sort order via AJAX.');
                    }
                })
                .catch(error => {
                    console.error('AJAX error:', error);
                });
            }
        });
    }
});
</script>
<style>
    .sortable-ghost {
        opacity: 0.4;
        background: #f8f9fa !important;
    }
</style>
<?php endif; ?>
