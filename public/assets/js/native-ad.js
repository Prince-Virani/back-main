
function previewImage(input, previewId) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.getElementById(previewId);
    img.src = e.target.result;
    img.style.display = 'block';
  };
  reader.readAsDataURL(file);
}

function submitNativeAd() {
  const form = document.getElementById('nativeAdForm');
  if (!form.checkValidity()) {
    form.classList.add('was-validated');
    return;
  }

  const overlay = document.getElementById('loadingOverlay');
  overlay.style.display = 'flex';

  const url   = form.action;
  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const data  = new FormData(form);

  fetch(url, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': token },
    body: data
  })
  .then(res => {
    if (res.ok) {
      window.location.href = window.nativeAdsIndexUrl;
    } else {
      return res.text().then(() => location.reload());
    }
  })
  .catch(() => location.reload());
}

$(document).ready(function() {

  $('.select2').select2({
    width: '100%',
    placeholder: 'Select an option',
    allowClear: true,
  }).on('select2:select select2:unselect', function() {
    const sel = $(this);
    const container = sel.next('.select2-container');
    const selection = container.find('.select2-selection');

    if (!sel.val() || sel.val().length === 0) {
      sel.addClass('is-invalid').removeClass('is-valid');
      selection.addClass('is-invalid').removeClass('is-valid');
    } else {
      sel.removeClass('is-invalid').addClass('is-valid');
      selection.removeClass('is-invalid').addClass('is-valid');
    }
  });

  $('.needs-validation').on('submit', function(e) {
    let valid = true;
    $(this).find('select[required]').each(function() {
      const sel = $(this);
      const container = sel.next('.select2-container');
      const selection = container.find('.select2-selection');

      if (!sel.val() || sel.val().length === 0) {
        sel.addClass('is-invalid').removeClass('is-valid');
        selection.addClass('is-invalid').removeClass('is-valid');
        valid = false;
      } else {
        sel.removeClass('is-invalid').addClass('is-valid');
        selection.removeClass('is-invalid').addClass('is-valid');
      }
    });

    if (!valid) {
      e.preventDefault();
      e.stopPropagation();
    }
    $(this).addClass('was-validated');
  });
});
