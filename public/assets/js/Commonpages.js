document.addEventListener('DOMContentLoaded', function () {
    const websiteSelect = document.getElementById('websiteFilter');
    const currentTab = document.querySelector('.nav-link.active').getAttribute('data-status');

    // Initial load
    fetchPages(websiteSelect.value, 2, 1);

    // Handle website filter change
    websiteSelect.addEventListener('change', function () {
        const activeTab = document.querySelector('.nav-link.active').getAttribute('data-status');
        fetchPages(this.value, activeTab, 1);
    });

    // Handle tab click
    document.querySelectorAll('.nav-link[data-status]').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.nav-link[data-status]').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const statusFlag = this.getAttribute('data-status');
            const websiteId = document.getElementById('websiteFilter').value;
            fetchPages(websiteId, statusFlag, 1);
        });
    });
});

function fetchPages(websiteId, statusFlag, page = 1) {
    const pagesTableBody = document.getElementById('pagesTableBody');
    const paginationWrapper = document.getElementById('paginationWrapper');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const searchTerm = document.getElementById('searchInput').value;
    pagesTableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center">⏳ Loading...</td>
        </tr>
    `;

    const url = `${indexpagesUrl}?website_id=${websiteId}&status_flag=${statusFlag}&page=${page}&search=${searchTerm}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            pagesTableBody.innerHTML = '';
            paginationWrapper.innerHTML = '';
            const dropdownToggle = document.querySelector('.dropdown-toggle.show');
            const dropdownMenu = document.querySelector('.dropdown-menu.show');

            if (dropdownToggle) dropdownToggle.classList.remove('show');
            if (dropdownMenu) dropdownMenu.classList.remove('show');
            if (data.data.length === 0) {
                pagesTableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">😔 No pages found.</td>
                    </tr>
                `;
                return;
            }

            data.data.forEach((commonpage, index) => {
                let editAction = '';
                let statusAction = '';

                // ✅ check edit-common-pages permission
                if (window.userPermissions.includes('edit-common-pages')) {
                    editAction = `<a href="${pagesUrl}/${commonpage.id}/edit" class="btn btn-theme">✏️ Edit</a>`;
                }

                // ✅ check delete-common-pages permission (apply on status toggle)
                if (window.userPermissions.includes('delete-common-pages')) {
                    statusAction = `
            <form action="${pagesUrl}/${commonpage.id}" method="POST" class="d-inline">
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="status_flag" value="${commonpage.status_flag == 0 ? 1 : 0}">
                <button type="submit" class="btn ${commonpage.status_flag == 0 ? 'btn-danger' : 'btn-success'}">
                    ${commonpage.status_flag == 0 ? 'Inactive' : 'Active'}
                </button>
            </form>`;
                }

                const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${commonpage.page_name}</td>
                    <td>${commonpage.website.website_name}</td>
                    <td>${commonpage.slug}</td>
                    <td>${editAction}</td>
                    <td>${statusAction}</td>
                </tr>
            `;

                pagesTableBody.insertAdjacentHTML('beforeend', row);
            });





            // Render pagination (Bootstrap-styled)
            const totalPages = data.last_page;
            const currentPage = data.current_page;

            const renderPageItem = (label, disabled, active = false, pageNumber = 1) => {
                return `
                    <li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${pageNumber}">${label}</a>
                    </li>
                `;
            };

            let paginationHTML = '';
            paginationHTML += renderPageItem('Previous', currentPage === 1, false, currentPage - 1);

            for (let i = 1; i <= totalPages; i++) {
                paginationHTML += renderPageItem(i, false, i === currentPage, i);
            }

            paginationHTML += renderPageItem('Next', currentPage === totalPages, false, currentPage + 1);
            paginationWrapper.innerHTML = `<ul class="pagination mb-0 justify-content-center">${paginationHTML}</ul>`;

            // Handle page change
            paginationWrapper.querySelectorAll('a.page-link').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const newPage = parseInt(this.getAttribute('data-page'));
                    if (!isNaN(newPage)) {
                        const websiteId = document.getElementById('websiteFilter').value;
                        const statusFlag = document.querySelector('.nav-link.active').getAttribute('data-status');
                        fetchPages(websiteId, statusFlag, newPage);
                    }
                });
            });

        })
        .catch(error => {
            console.error('Error fetching pages:', error);
            pagesTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger">❌ Failed to load data. Try again later.</td>
                </tr>
            `;
        });
}
function searchPages(value) {
    const websiteId = document.getElementById('websiteFilter').value;
    const statusFlag = document.getElementById('statusFilter').value;
    fetchPages(websiteId, statusFlag);
}