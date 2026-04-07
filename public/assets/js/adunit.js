let debounceTimeout;
const CREATE_URL = window.ADUNIT_CREATE_URL || '/create-ad-unit';
const DELETE_URL = '/adunits';

function getCsrf() {
  const m = document.querySelector('meta[name="csrf-token"]');
  return m ? m.getAttribute('content') : '';
}

async function toJson(res) {
  const ct = res.headers.get('content-type') || '';
  if (ct.includes('application/json')) return res.json();
  const t = await res.text();
  try { return JSON.parse(t); } catch { return {}; }
}

function successPayload(res, data) {
  return res.ok && (
    data?.ok === true ||
    data?.success === true ||
    !!data?.data ||
    data?.status === 'ok'
  );
}
document.addEventListener('click', async (e) => {
    if (!e.target.closest('.delete-adunit-btn')) return;
    
    const btn = e.target.closest('.delete-adunit-btn');
    const adunitId = btn.dataset.adunitId;
    const websiteId = btn.dataset.websiteId;
    const adunitName = btn.dataset.adunitName;
    
    if (!confirm(`Are you sure you want to delete ad unit "${adunitName}"? This action cannot be undone.`)) {
        return;
    }
    
    if (btn.dataset.busy === '1') return;
    
    btn.dataset.busy = '1';
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    try {
        const res = await fetch(`${DELETE_URL}/${adunitId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                website_id: websiteId
            }),
            credentials: 'same-origin'
        });
        
        if (res.status === 419) { 
            location.reload(); 
            return; 
        }
        
        const data = await toJson(res);
        
        if (successPayload(res, data)) {
            const row = btn.closest('tr');
            if (row) {
                row.remove();
            }
            alert('Ad unit deleted successfully.');
        } else {
            alert(data?.message || data?.error || 'Failed to delete ad unit.');
        }
    } catch (error) {
        alert('Network error. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i>';
        btn.dataset.busy = '0';
    }
});

document.addEventListener('change', async (e) => {
  if (!e.target.matches('.toggle-status-checkbox, .toggle-lazy-checkbox')) return;
  const checkbox = e.target;
  const form = checkbox.closest('form');
  if (!form || checkbox.dataset.busy === '1') return;

  const fd = new FormData(form);
  const url = form.dataset.url;

  checkbox.dataset.busy = '1';
  checkbox.disabled = true;

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': fd.get('_token') || getCsrf()
      },
      body: fd,
      credentials: 'same-origin'
    });

    if (res.status === 419) { location.reload(); return; }

    const data = await toJson(res);
    if (!successPayload(res, data)) {
      checkbox.checked = !checkbox.checked;
      alert(data?.message || data?.error || 'Failed to update.');
    }
  } catch (_) {
    checkbox.checked = !checkbox.checked;
    alert('Network error. Please try again.');
  } finally {
    checkbox.disabled = false;
    checkbox.dataset.busy = '0';
  }
});

function debounceCreateAdUnit(event, websiteId) {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => createAdUnit(event, websiteId), 400);
}

async function createAdUnit(event, websiteId) {
  event.preventDefault();

  const nameInput = document.getElementById(`adunit-name-${websiteId}`);
  const positionSelect = document.getElementById(`position-${websiteId}`);
  const codeInput = document.getElementById(`adunit-code-${websiteId}`);
  const inPageInput = document.getElementById(`adunit-in-page-${websiteId}`);
  const resultContainer = document.getElementById(`create-result-${websiteId}`);

  const name = (nameInput?.value || '').trim();
  const position = (positionSelect?.value || '').trim();
  const code = (codeInput?.value || '').trim();
  const in_page_position = parseInt((inPageInput?.value || '0'), 10) || 0;

  if (!name || !position || !code) {
    resultContainer.className = 'small text-danger';
    resultContainer.textContent = 'All fields are required.';
    return;
  }

  resultContainer.className = 'small';
  resultContainer.textContent = 'Creating...';

  const btn = event.submitter || event.target.querySelector('[type="submit"]');
  if (btn) btn.disabled = true;

  try {
    const res = await fetch(CREATE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': getCsrf()
      },
      body: JSON.stringify({
        name,
        adunit_name: name,
        position,
        website_id: websiteId,
        code,
        adunit_code: code,
        in_page_position
      }),
      credentials: 'same-origin'
    });

    if (res.status === 419) { location.reload(); return; }

    const data = await toJson(res);

    if (successPayload(res, data)) {
      resultContainer.className = 'small text-success';
      resultContainer.textContent = 'Ad unit created.';
      setTimeout(() => location.reload(), 600);
    } else {
      resultContainer.className = 'small text-danger';
      resultContainer.textContent = data?.message || (data?.errors && Object.values(data.errors).flat()[0]) || data?.debug || 'Failed';
    }
  } catch (_) {
    resultContainer.className = 'small text-danger';
    resultContainer.textContent = 'Request failed';
  } finally {
    if (btn) btn.disabled = false;
  }
}
