
document.addEventListener("DOMContentLoaded", function () {

    /* =====================================
       PROFILE DROPDOWN
    ===================================== */

    const profileButton =
        document.getElementById("profileMenuButton");

    const profileDropdown =
        document.getElementById("profileDropdown");

    if (profileButton && profileDropdown) {

        profileButton.addEventListener("click", function (event) {
            event.stopPropagation();

            profileDropdown.classList.toggle("show");
        });

        document.addEventListener("click", function (event) {

            if (
                !profileDropdown.contains(event.target) &&
                !profileButton.contains(event.target)
            ) {
                profileDropdown.classList.remove("show");
            }

        });

    }


    /* =====================================
       MOBILE SIDEBAR
    ===================================== */

    const menuButton =
        document.getElementById("mobileMenuButton");

    const sidebar =
        document.querySelector(".sidebar");

    const overlay =
        document.getElementById("sidebarOverlay");

    function closeSidebar() {

        if (sidebar) {
            sidebar.classList.remove("mobile-open");
        }

        if (overlay) {
            overlay.classList.remove("show");
        }

    }

    if (menuButton && sidebar) {

        menuButton.addEventListener("click", function () {

            sidebar.classList.toggle("mobile-open");

            if (overlay) {
                overlay.classList.toggle("show");
            }

        });

    }

    if (overlay) {
        overlay.addEventListener("click", closeSidebar);
    }


    /* =====================================
       CONFIRMATION MODALS
    ===================================== */

    document.querySelectorAll("[data-confirm]").forEach(function (element) {

        element.addEventListener("click", function (event) {

            const message =
                element.dataset.confirm ||
                "Are you sure you want to continue?";

            if (!window.confirm(message)) {
                event.preventDefault();
            }

        });

    });


    /* =====================================
       AUTO HIDE FLASH MESSAGES
    ===================================== */

    const flashMessages =
        document.querySelectorAll(".flash-message");

    flashMessages.forEach(function (message) {

        setTimeout(function () {
            message.classList.add("flash-hide");
        }, 3500);

    });


    /* =====================================
       GENERIC MODAL SYSTEM
    ===================================== */

    document.querySelectorAll("[data-modal-open]").forEach(function (button) {

        button.addEventListener("click", function () {

            const modalId =
                button.getAttribute("data-modal-open");

            const modal =
                document.getElementById(modalId);

            if (modal) {
                modal.classList.add("show");
            }

        });

    });


    document.querySelectorAll("[data-modal-close]").forEach(function (button) {

        button.addEventListener("click", function () {

            const modal =
                button.closest(".modal-overlay");

            if (modal) {
                modal.classList.remove("show");
            }

        });

    });


    document.querySelectorAll(".modal-overlay").forEach(function (modal) {

        modal.addEventListener("click", function (event) {

            if (event.target === modal) {
                modal.classList.remove("show");
            }

        });

    });


    /* =====================================
   GLOBAL SEARCH
===================================== */

const globalSearch =
    document.getElementById("globalSearch");

const searchContainer =
    document.querySelector(".topbar-search");

let globalSearchResults = null;
let searchTimer = null;

if (globalSearch && searchContainer) {

    globalSearchResults =
        document.createElement("div");

    globalSearchResults.className =
        "global-search-results";

    searchContainer.appendChild(
        globalSearchResults
    );


    globalSearch.addEventListener(
        "input",
        function () {

            const query =
                globalSearch.value.trim();

            clearTimeout(searchTimer);

            if (query.length < 2) {

                globalSearchResults.innerHTML = "";
                globalSearchResults.classList.remove("show");

                return;
            }


            searchTimer = setTimeout(
                function () {

                    fetch(
                        "global_search.php?q=" +
                        encodeURIComponent(query)
                    )
                        .then(function (response) {

                            if (!response.ok) {
                                throw new Error(
                                    "Search request failed."
                                );
                            }

                            return response.json();
                        })

                        .then(function (results) {

                            globalSearchResults.innerHTML = "";

                            if (
                                !Array.isArray(results) ||
                                results.length === 0
                            ) {

                                globalSearchResults.innerHTML = `
                                    <div class="global-search-empty">
                                        No results found for
                                        <strong>${escapeSearchText(query)}</strong>
                                    </div>
                                `;

                                globalSearchResults.classList.add("show");

                                return;
                            }


                            results.forEach(function (result) {

                                const link =
                                    document.createElement("a");

                                link.href =
                                    result.url;

                                link.className =
                                    "global-search-result-item";


                                const icon =
                                    document.createElement("span");

                                icon.className =
                                    "global-search-result-icon";

                                icon.textContent =
                                    result.icon || "⌕";


                                const content =
                                    document.createElement("span");

                                content.className =
                                    "global-search-result-content";


                                const title =
                                    document.createElement("strong");

                                title.textContent =
                                    result.title;


                                const meta =
                                    document.createElement("small");

                                meta.textContent =
                                    result.meta;


                                content.appendChild(title);
                                content.appendChild(meta);

                                link.appendChild(icon);
                                link.appendChild(content);

                                globalSearchResults.appendChild(link);

                            });


                            globalSearchResults.classList.add("show");

                        })

                        .catch(function () {

                            globalSearchResults.innerHTML = `
                                <div class="global-search-empty">
                                    Unable to search right now.
                                </div>
                            `;

                            globalSearchResults.classList.add("show");

                        });

                },
                250
            );

        }
    );


    globalSearch.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Escape") {

                globalSearchResults.classList.remove(
                    "show"
                );

                globalSearch.blur();
            }


            if (event.key === "Enter") {

                const firstResult =
                    globalSearchResults.querySelector(
                        ".global-search-result-item"
                    );

                if (firstResult) {

                    event.preventDefault();

                    window.location.href =
                        firstResult.href;
                }
            }

        }
    );


    document.addEventListener(
        "click",
        function (event) {

            if (
                !searchContainer.contains(event.target)
            ) {

                globalSearchResults.classList.remove(
                    "show"
                );
            }

        }
    );

}


function escapeSearchText(value) {

    const element =
        document.createElement("div");

    element.textContent = value;

    return element.innerHTML;
}

});


/* =====================================
   TOAST FUNCTION
===================================== */

function showToast(message, type = "success") {

    const container =
        document.getElementById("toastContainer");

    if (!container) {
        return;
    }

    const toast =
        document.createElement("div");

    toast.className =
        "app-toast " + type;

    toast.innerHTML = `
        <span>${escapeToastText(message)}</span>
        <button type="button">&times;</button>
    `;

    container.appendChild(toast);

    const closeButton =
        toast.querySelector("button");

    closeButton.addEventListener("click", function () {
        toast.remove();
    });

    setTimeout(function () {

        toast.classList.add("toast-out");

        setTimeout(function () {
            toast.remove();
        }, 250);

    }, 3000);

}


function escapeToastText(value) {

    const element =
        document.createElement("div");

    element.textContent = value;

    return element.innerHTML;

}