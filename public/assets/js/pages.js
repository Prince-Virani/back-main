const PER_PAGE = 50;

document.addEventListener('DOMContentLoaded', function () {
  const websiteSelect = document.getElementById('websiteFilter');
  const statusSelect = document.getElementById('statusFilter');
  const tabs = document.querySelectorAll('.nav-link[data-status]');
  const searchInput = document.getElementById('searchInput');

  const activeTab = document.querySelector('.nav-link[data-status].active');
  const startStatus = activeTab ? activeTab.getAttribute('data-status') : '2';
  fetchPages(websiteSelect.value, startStatus, 1);

  websiteSelect.addEventListener('change', function () {
    fetchPages(this.value, getActiveStatus(), 1);
    closeOpenDropdown();
  });

  statusSelect.addEventListener('change', function () {
    setActiveTabByStatus(this.value);
    fetchPages(websiteSelect.value, this.value, 1);
    closeOpenDropdown();
  });

  tabs.forEach(tab => {
    tab.addEventListener('click', function () {
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      const s = this.getAttribute('data-status');
      statusSelect.value = s;
      fetchPages(websiteSelect.value, s, 1);
    });
  });

  let t;
  searchInput.addEventListener('input', function () {
    clearTimeout(t);
    t = setTimeout(() => {
      fetchPages(websiteSelect.value, getActiveStatus(), 1);
    }, 250);
  });
});

function getActiveStatus() {
  const t = document.querySelector('.nav-link[data-status].active');
  return t ? t.getAttribute('data-status') : '2';
}

function setActiveTabByStatus(s) {
  document.querySelectorAll('.nav-link[data-status]').forEach(t => {
    t.classList.toggle('active', t.getAttribute('data-status') === String(s));
  });
}

function closeOpenDropdown() {
  const toggle = document.querySelector('.dropdown-toggle.show');
  const menu = document.querySelector('.dropdown-menu.show');
  if (toggle) toggle.classList.remove('show');
  if (menu) menu.classList.remove('show');
}

function fetchPages(websiteId, statusFlag, page = 1) {
  const pagesTableBody = document.getElementById('pagesTableBody');
  const paginationWrapper = document.getElementById('paginationWrapper');
  const entryCountText = document.getElementById('entryCountText');
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
  const searchTerm = document.getElementById('searchInput').value || '';

  pagesTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4">⏳ Loading...</td></tr>';

  const params = new URLSearchParams({
    website_id: websiteId || 0,
    status_flag: statusFlag || 2,
    page: page || 1,
    per_page: PER_PAGE,
    search: searchTerm
  });

  fetch(`${indexpagesUrl}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
      pagesTableBody.innerHTML = '';
      paginationWrapper.innerHTML = '';

      const rows = Array.isArray(data.data) ? data.data : (data.items || []);
      const current = data.current_page || (data.meta && data.meta.current_page) || page || 1;
      const last = data.last_page || (data.meta && data.meta.last_page) || 1;
      const total = data.total || (data.meta && data.meta.total) || (rows ? rows.length : 0);
      const per = data.per_page || (data.meta && data.meta.per_page) || PER_PAGE;
      const from = data.from || (data.meta && data.meta.from) || (total ? (current - 1) * per + 1 : 0);
      const to = data.to || (data.meta && data.meta.to) || Math.min(current * per, total);

      if (!rows || rows.length === 0) {
        pagesTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4">😔 No pages found.</td></tr>';
        entryCountText.textContent = 'No entries';
        return;
      }

      rows.forEach(pageItem => {
        const id = pageItem.id ?? '';
        const name = pageItem.name ?? pageItem.title ?? `#${id}`;
        const category = pageItem.categories ?? pageItem.category_name ?? '-';
        const editUrl = `${pagesUrl}/${id}/edit`;
        const isActive = String(pageItem.status_flag) === '0';

        let editAction = '';
        let statusAction = '';

        // ✅ edit-pages permission
        if (window.userPermissions.includes('edit-pages')) {
          editAction = `<a href="${editUrl}" class="btn btn-sm btn-outline-primary"><i class="fa fa-pen"></i></a>`;
        }

        // ✅ edit-status permission
        if (window.userPermissions.includes('edit-status')) {
          statusAction = `
          <form action="${pagesUrl}/${id}" method="POST" class="d-inline">
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="status_flag" value="${isActive ? 1 : 0}">
            <button type="submit" class="btn btn-sm ${isActive ? 'btn-danger' : 'btn-success'}">
              ${isActive ? 'Inactive' : 'Active'}
            </button>
          </form>`;
        }

        const row = `
      <tr>
        <td>${id ?? ''}</td>
        <td>${escapeHtml(name)}</td>
        <td>${escapeHtml(category)}</td>
        <td>${editAction}</td>   <!-- separate column for edit -->
        <td>${statusAction}</td> <!-- separate column for status -->
      </tr>`;

        pagesTableBody.insertAdjacentHTML('beforeend', row);
      });


      entryCountText.textContent = total ? `Showing ${from}–${to} of ${total}` : 'No entries';

      renderPagination(paginationWrapper, current, last, (newPage) => {
        const s = getActiveStatus();
        const w = document.getElementById('websiteFilter').value;
        fetchPages(w, s, newPage);
        document.getElementById('currentPage').value = String(newPage);
      });

      document.getElementById('currentPage').value = String(current);
    })
    .catch(() => {
      pagesTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">❌ Failed to load data. Try again later.</td></tr>';
    });
}

function renderPagination(wrapper, current, last, onChange) {
  const items = [];
  items.push(paginationItem('Prev', current > 1, () => onChange(current - 1)));

  let start = Math.max(1, current - 2);
  let end = Math.min(last, start + 4);
  start = Math.max(1, end - 4);

  for (let p = start; p <= end; p++) {
    items.push(pageNumberItem(p, p === current, () => onChange(p)));
  }

  items.push(paginationItem('Next', current < last, () => onChange(current + 1)));
  wrapper.innerHTML = items.join('');
}

function paginationItem(label, enabled, onClick) {
  const disabledClass = enabled ? '' : ' disabled';
  const handler = enabled ? `onclick="${registerTempHandler(onClick)}"` : '';
  return `<li class="page-item${disabledClass}"><a class="page-link" href="javascript:void(0)" ${handler}>${escapeHtml(label)}</a></li>`;
}

function pageNumberItem(p, active, onClick) {
  const activeClass = active ? ' active' : '';
  return `<li class="page-item${activeClass}"><a class="page-link" href="javascript:void(0)" onclick="${registerTempHandler(onClick)}">${p}</a></li>`;
}

function registerTempHandler(fn) {
  const key = `h_${Math.random().toString(36).slice(2)}`;
  window[key] = function () { try { fn(); } finally { delete window[key]; } };
  return `${key}()`;
}

function escapeHtml(s) {
  return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
