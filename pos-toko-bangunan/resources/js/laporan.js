document.addEventListener("DOMContentLoaded", function () {
    const filterButton = document.getElementById("btn-filter");
    const searchBar = document.getElementById("search-bar");

    filterButton.addEventListener("click", () => {
        const startDate = document.getElementById("start-date").value;
        const endDate = document.getElementById("end-date").value;
        const keyword = searchBar.value;

        // Placeholder log
        console.log("Filter diklik:", { startDate, endDate, keyword });

        // Di sinilah nanti fetch ke backend akan dilakukan
    });
    
});
document.getElementById("export-pdf").addEventListener("click", () => {
    console.log("Export PDF diklik");
    // Nanti hubungkan ke backend atau library untuk ekspor PDF
});

document.getElementById("export-excel").addEventListener("click", () => {
    console.log("Export Excel diklik");
    // Nanti hubungkan ke backend atau library untuk ekspor Excel
});
