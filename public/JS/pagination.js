document.addEventListener("DOMContentLoaded", function () {
    const transactions = Array.from(document.querySelectorAll(".transaction-item"));
    const itemsPerPage = 10;
    let currentPage = 1;

    function renderPage(page) {
        const start = (page - 1) * itemsPerPage;
        const end = page * itemsPerPage;

        transactions.forEach((transaction, index) => {
            if (index >= start && index < end) {
                transaction.style.display = "block";
            } else {
                transaction.style.display = "none";
            }
        });

        document.getElementById("page-info").textContent = `Page ${page}`;
    }

    document.getElementById("prev-page").addEventListener("click", function () {
        if (currentPage > 1) {
            currentPage--;
            renderPage(currentPage);
        }
    });

    document.getElementById("next-page").addEventListener("click", function () {
        if (currentPage * itemsPerPage < transactions.length) {
            currentPage++;
            renderPage(currentPage);
        }
    });

    renderPage(currentPage);
});