<?php
$flash = $_SESSION['flash'] ?? null;
if ($flash && isset($flash['text'])) {
    $type = $flash['type'] ?? 'success';
    $text = htmlspecialchars($flash['text'], ENT_QUOTES, 'UTF-8');
    unset($_SESSION['flash']);
?>
<div class="toast toast-<?= htmlspecialchars($type) ?>" role="status" aria-live="polite" aria-atomic="true">
    <span class="toast-icon" aria-hidden="true">
        <?php if ($type === 'success'): ?>✅<?php elseif ($type === 'error'): ?>⚠️<?php else: ?>ℹ️<?php endif; ?>
    </span>
    <span class="toast-text"><?= $text ?></span>
    <button class="toast-close" type="button" aria-label="Close">✖</button>
</div>
<script>
(function(){
  var toast = document.querySelector('.toast');
  if (!toast) return;
  var close = toast.querySelector('.toast-close');
  var hide = function(){ if (toast && toast.parentNode) toast.parentNode.removeChild(toast); };
  var timer = setTimeout(hide, 4000);
  if (close) { close.addEventListener('click', function(){ clearTimeout(timer); hide(); }); }
})();
</script>
<?php }
?>