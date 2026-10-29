/* =========================================================
   STUDENTHUB
   Main JavaScript
   Theme + Navigation + Animations
========================================================= */

"use strict";


/* =========================================================
   DOM READY
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    initializeTheme();

    initializeThemeSwitcher();

    initializeNavbar();

    initializeScrollReveal();

    initializeCounters();

    initializeParallax();

    initializePageLoader();

    initializeCurrentYear();

    initializeHoverEffects();

    initializePasswordToggles();

    initializeAuthForms();

    initializeProgressBars();

    initializeProfileForm();

    initializeDashboard();

    initializeEventFilters();

    initializeFaq();

    initializeFeedbackForm();

    initializeContactForm();

    initializeAdminPortal();

});


/* =========================================================
   THEME SYSTEM
========================================================= */

const THEME_KEY = "studenthub-theme";


function initializeTheme() {

    const savedTheme =
        localStorage.getItem(THEME_KEY);

    if (savedTheme === "blue") {

        document.body.classList.remove("theme-teal");

        document.body.classList.add("theme-blue");

    } else {

        document.body.classList.remove("theme-blue");

        document.body.classList.add("theme-teal");

    }

    updateThemeButtons(
        savedTheme || "teal"
    );

}


/* =========================================================
   THEME SWITCHER
========================================================= */

function initializeThemeSwitcher() {

    const buttons =
        document.querySelectorAll(".theme-btn");

    if (!buttons.length) return;


    buttons.forEach(button => {

        button.addEventListener("click", () => {

            const selectedTheme =
                button.dataset.theme;

            if (!selectedTheme) return;

            changeTheme(selectedTheme);

        });

    });

}


function changeTheme(theme) {

    if (
        theme !== "teal" &&
        theme !== "blue"
    ) {

        return;

    }


    /*
       Add temporary transition class
       for premium theme animation.
    */

    document.body.classList.add(
        "theme-transition"
    );


    /*
       Change theme
    */

    if (theme === "blue") {

        document.body.classList.remove(
            "theme-teal"
        );

        document.body.classList.add(
            "theme-blue"
        );

    } else {

        document.body.classList.remove(
            "theme-blue"
        );

        document.body.classList.add(
            "theme-teal"
        );

    }


    /*
       Save theme
    */

    localStorage.setItem(
        THEME_KEY,
        theme
    );


    /*
       Update buttons
    */

    updateThemeButtons(theme);


    /*
       Remove transition class
    */

    setTimeout(() => {

        document.body.classList.remove(
            "theme-transition"
        );

    }, 1200);

}


function updateThemeButtons(theme) {

    const buttons =
        document.querySelectorAll(".theme-btn");


    buttons.forEach(button => {

        button.classList.remove(
            "active"
        );


        if (
            button.dataset.theme === theme
        ) {

            button.classList.add(
                "active"
            );

        }

    });

}


/* =========================================================
   NAVBAR
========================================================= */

function initializeNavbar() {

    const header =
        document.querySelector(".header");

    if (!header) return;


    window.addEventListener(
        "scroll",
        () => {

            if (window.scrollY > 40) {

                header.classList.add(
                    "scrolled"
                );

            } else {

                header.classList.remove(
                    "scrolled"
                );

            }

        },
        {
            passive: true
        }
    );

}


/* =========================================================
   SCROLL REVEAL
========================================================= */

function initializeScrollReveal() {

    const elements =
        document.querySelectorAll(
            ".section, .quick-card, .feature-card, .event-card, .announcement-card, .testimonial-card, .analytics-item, .achievement-item"
        );


    if (!elements.length) return;


    elements.forEach(element => {

        element.classList.add(
            "reveal"
        );

    });


    const observer =
        new IntersectionObserver(
            entries => {

                entries.forEach(entry => {

                    if (
                        entry.isIntersecting
                    ) {

                        entry.target.classList.add(
                            "visible"
                        );

                        observer.unobserve(
                            entry.target
                        );

                    }

                });

            },
            {
                threshold: 0.12
            }
        );


    elements.forEach(element => {

        observer.observe(element);

    });

}


/* =========================================================
   PROGRESS BARS + RINGS
   Animates any [data-progress] element into view.
   .dash-ring drives the conic ring via --dash-ring-val;
   every other target animates its width. Runtime-only
   styling (no inline CSS lives in the HTML source).
========================================================= */

function initializeProgressBars() {

    const bars =
        document.querySelectorAll(
            "[data-progress]"
        );


    if (!bars.length) return;


    const applyValue = element => {

        const raw =
            parseFloat(
                element.dataset.progress
            ) || 0;

        const value =
            Math.max(
                0,
                Math.min(100, raw)
            );

        if (
            element.classList.contains("dash-ring")
        ) {

            element.style.setProperty(
                "--dash-ring-val",
                value
            );

        } else {

            element.style.width =
                value + "%";

        }

    };


    if (
        !("IntersectionObserver" in window)
    ) {

        bars.forEach(applyValue);

        return;

    }


    const observer =
        new IntersectionObserver(
            entries => {

                entries.forEach(entry => {

                    if (
                        !entry.isIntersecting
                    ) {

                        return;

                    }

                    applyValue(entry.target);

                    observer.unobserve(
                        entry.target
                    );

                });

            },
            {
                threshold: 0.3
            }
        );


    bars.forEach(bar => {

        observer.observe(bar);

    });

}


/* =========================================================
   NUMBER COUNTERS
========================================================= */

function initializeCounters() {

    const counters =
        document.querySelectorAll(
            "[data-count]"
        );


    if (!counters.length) return;


    const observer =
        new IntersectionObserver(
            entries => {

                entries.forEach(entry => {

                    if (
                        !entry.isIntersecting
                    ) {

                        return;

                    }


                    const element =
                        entry.target;

                    const target =
                        Number(
                            element.dataset.count
                        );


                    if (
                        Number.isNaN(target)
                    ) {

                        return;

                    }


                    animateCounter(
                        element,
                        target
                    );


                    observer.unobserve(
                        element
                    );

                });

            },
            {
                threshold: 0.7
            }
        );


    counters.forEach(counter => {

        observer.observe(counter);

    });

}


function animateCounter(
    element,
    target
) {

    const duration = 1800;

    const startTime =
        performance.now();


    function update(currentTime) {

        const elapsed =
            currentTime - startTime;


        const progress =
            Math.min(
                elapsed / duration,
                1
            );


        /*
           Smooth easing
        */

        const eased =
            1 -
            Math.pow(
                1 - progress,
                3
            );


        const current =
            Math.floor(
                target * eased
            );


        element.textContent =
            current.toLocaleString() + "+";


        if (progress < 1) {

            requestAnimationFrame(
                update
            );

        } else {

            element.textContent =
                target.toLocaleString() + "+";

        }

    }


    requestAnimationFrame(
        update
    );

}


/* =========================================================
   GRADUATION CAP PARALLAX
========================================================= */

function initializeParallax() {

    const scene =
        document.querySelector(
            ".graduation-scene"
        );

    const cap =
        document.querySelector(
            ".floating-cap"
        );


    if (!scene || !cap) return;


    /*
       Disable heavy movement on
       touch devices.
    */

    if (
        window.matchMedia(
            "(pointer: coarse)"
        ).matches
    ) {

        return;

    }


    window.addEventListener(
        "mousemove",
        event => {

            const x =
                (
                    event.clientX /
                    window.innerWidth
                ) - 0.5;


            const y =
                (
                    event.clientY /
                    window.innerHeight
                ) - 0.5;


            const rotateX =
                y * -12;

            const rotateY =
                x * 12;


            cap.style.transform =
                `
                translateY(0)
                rotateX(${rotateX}deg)
                rotateY(${rotateY}deg)
                `;


            scene.style.setProperty(
                "--mouse-x",
                `${event.clientX}px`
            );


            scene.style.setProperty(
                "--mouse-y",
                `${event.clientY}px`
            );

        }
    );

}


/* =========================================================
   PAGE LOADER
========================================================= */

function initializePageLoader() {

    window.addEventListener(
        "load",
        () => {

            document.body.classList.add(
                "page-loaded"
            );

        }
    );

}


/* =========================================================
   CURRENT YEAR
========================================================= */

function initializeCurrentYear() {

    const yearElements =
        document.querySelectorAll(
            "[data-year]"
        );


    const currentYear =
        new Date().getFullYear();


    yearElements.forEach(
        element => {

            element.textContent =
                currentYear;

        }
    );

}


/* =========================================================
   CARD HOVER EFFECT
========================================================= */

function initializeHoverEffects() {

    const cards =
        document.querySelectorAll(
            ".quick-card, .feature-card, .event-card, .testimonial-card, .announcement-card"
        );


    if (!cards.length) return;


    cards.forEach(card => {

        card.addEventListener(
            "mousemove",
            event => {

                const rect =
                    card.getBoundingClientRect();


                const x =
                    event.clientX -
                    rect.left;


                const y =
                    event.clientY -
                    rect.top;


                card.style.setProperty(
                    "--mouse-x",
                    `${x}px`
                );


                card.style.setProperty(
                    "--mouse-y",
                    `${y}px`
                );

            }
        );


        card.addEventListener(
            "mouseleave",
            () => {

                card.style.removeProperty(
                    "--mouse-x"
                );

                card.style.removeProperty(
                    "--mouse-y"
                );

            }
        );

    });

}


/* =========================================================
   ESC KEY SUPPORT
========================================================= */

document.addEventListener(
    "keydown",
    event => {

        if (event.key === "Escape") {

            document.body.classList.remove(
                "menu-open"
            );

        }

    }
);


/* =========================================================
   PAGE VISIBILITY
========================================================= */

document.addEventListener(
    "visibilitychange",
    () => {

        if (
            document.hidden
        ) {

            document.body.classList.add(
                "page-hidden"
            );

        } else {

            document.body.classList.remove(
                "page-hidden"
            );

        }

    }
);


/* =========================================================
   PREVENT DOUBLE FORM SUBMISSION
========================================================= */

document.addEventListener(
    "submit",
    event => {

        const form =
            event.target;


        if (
            !form.matches(
                "form"
            )
        ) {

            return;

        }


        const submitButton =
            form.querySelector(
                "[type='submit']"
            );


        if (!submitButton) return;


        /*
           Don't permanently disable
           forms that use custom JS.
        */

        if (
            form.dataset.preventDoubleSubmit ===
            "true"
        ) {

            if (
                submitButton.dataset.submitted ===
                "true"
            ) {

                event.preventDefault();

                return;

            }


            submitButton.dataset.submitted =
                "true";


            submitButton.style.opacity =
                ".7";

        }

    }
);


/* =========================================================
   CONSOLE BRANDING
========================================================= */

console.log(
    "%c StudentHub ",
    `
    background:#18c8a7;
    color:#031312;
    font-size:20px;
    font-weight:800;
    padding:8px 16px;
    border-radius:8px;
    `
);

console.log(
    "CHARUSAT Student Portal initialized."
);
/* =========================================================
   ADMIN SIDEBAR
========================================================= */

function initializeAdminSidebar() {

    const sidebar =
        document.getElementById("adminSidebar");

    const toggle =
        document.getElementById("adminMenuToggle");

    if (!sidebar || !toggle) return;

    toggle.addEventListener("click", () => {

        sidebar.classList.toggle("open");

        const icon =
            toggle.querySelector("i");

        if (!icon) return;

        if (sidebar.classList.contains("open")) {

            icon.classList.remove(
                "fa-bars"
            );

            icon.classList.add(
                "fa-xmark"
            );

        } else {

            icon.classList.remove(
                "fa-xmark"
            );

            icon.classList.add(
                "fa-bars"
            );

        }

    });


    /* Close sidebar after clicking link */

    const links =
        sidebar.querySelectorAll(
            ".admin-nav-link"
        );

    links.forEach(link => {

        link.addEventListener("click", () => {

            if (
                window.innerWidth <= 1100
            ) {

                sidebar.classList.remove(
                    "open"
                );

                const icon =
                    toggle.querySelector("i");

                if (icon) {

                    icon.classList.remove(
                        "fa-xmark"
                    );

                    icon.classList.add(
                        "fa-bars"
                    );

                }

            }

        });

    });

}


/* Run admin sidebar */

initializeAdminSidebar();
/* =========================================================
   STUDENT MANAGEMENT
========================================================= */

function initializeStudentManagement(){

    const table =
        document.getElementById("studentsTable");

    if(!table) return;


    const search =
        document.getElementById("studentSearch");

    const topSearch =
        document.getElementById("topStudentSearch");

    const department =
        document.getElementById("departmentFilter");

    const semester =
        document.getElementById("semesterFilter");

    const status =
        document.getElementById("statusFilter");

    const rows =
        [...table.querySelectorAll("tbody tr")];

    function filterStudents(value = ""){

        const query =
            value.toLowerCase().trim();

        const departmentValue =
            department?.value || "all";

        const semesterValue =
            semester?.value || "all";

        const statusValue =
            status?.value || "all";


        rows.forEach(row => {

            const name =
                (row.dataset.name || "").toLowerCase();

            const id =
                (row.dataset.id || "").toLowerCase();

            const rowDepartment =
                row.dataset.department || "";

            const rowSemester =
                row.dataset.semester || "";

            const rowStatus =
                row.dataset.status || "";


            const matchesSearch =
                !query ||
                name.includes(query) ||
                id.toLowerCase().includes(query) ||
                row.innerText.toLowerCase().includes(query);


            const matchesDepartment =
                departmentValue === "all" ||
                rowDepartment === departmentValue;


            const matchesSemester =
                semesterValue === "all" ||
                rowSemester === semesterValue;


            const matchesStatus =
                statusValue === "all" ||
                rowStatus === statusValue;


            const visible =
                matchesSearch &&
                matchesDepartment &&
                matchesSemester &&
                matchesStatus;


            row.style.display =
                visible ? "" : "none";

        });

    }


    search?.addEventListener(
        "input",
        () => filterStudents(search.value)
    );


    topSearch?.addEventListener(
        "input",
        () => {

            if(search){
                search.value =
                    topSearch.value;
            }

            filterStudents(topSearch.value);

        }
    );


    department?.addEventListener(
        "change",
        () => filterStudents(search?.value || "")
    );


    semester?.addEventListener(
        "change",
        () => filterStudents(search?.value || "")
    );


    status?.addEventListener(
        "change",
        () => filterStudents(search?.value || "")
    );


    /* ==========================================
       SELECT ALL
    ========================================== */

    const selectAll =
        document.getElementById(
            "selectAllStudents"
        );

    const count =
        document.getElementById(
            "selectedStudentCount"
        );


    function updateSelectedCount(){

        const selected =
            table.querySelectorAll(
                ".student-checkbox:checked"
            ).length;

        if(count){

            count.textContent =
                `${selected} selected`;

        }

    }


    selectAll?.addEventListener(
        "change",
        () => {

            const visibleRows =
                rows.filter(
                    row =>
                        row.style.display !== "none"
                );

            visibleRows.forEach(row => {

                const checkbox =
                    row.querySelector(
                        ".student-checkbox"
                    );

                if(checkbox){
                    checkbox.checked =
                        selectAll.checked;
                }

            });

            updateSelectedCount();

        }
    );


    table.addEventListener(
        "change",
        event => {

            if(
                event.target.classList.contains(
                    "student-checkbox"
                )
            ){

                updateSelectedCount();

            }

        }
    );


    /* ==========================================
       VIEW STUDENT MODAL
    ========================================== */

    const modal =
        document.getElementById(
            "studentViewModal"
        );

    const closeModal =
        document.getElementById(
            "closeStudentModal"
        );

    const modalCloseButton =
        document.getElementById(
            "modalCloseButton"
        );


    function openStudentModal(row){

        if(!modal) return;


        const name =
            row.dataset.name || "Student";

        const id =
            row.dataset.id || "-";

        const dept =
            row.dataset.department || "-";

        const sem =
            row.dataset.semester || "-";

        const statusValue =
            row.dataset.status || "-";


        const emailCell =
            row.children[5];

        const email =
            emailCell
            ? emailCell.textContent.trim()
            : "-";


        const avatar =
            document.getElementById(
                "modalStudentAvatar"
            );

        const nameElement =
            document.getElementById(
                "modalStudentName"
            );

        const idElement =
            document.getElementById(
                "modalStudentId"
            );

        const emailElement =
            document.getElementById(
                "modalStudentEmail"
            );

        const departmentElement =
            document.getElementById(
                "modalStudentDepartment"
            );

        const semesterElement =
            document.getElementById(
                "modalStudentSemester"
            );

        const statusElement =
            document.getElementById(
                "modalStudentStatus"
            );


        const initials =
            name
                .split(" ")
                .map(word => word[0])
                .slice(0,2)
                .join("")
                .toUpperCase();


        if(avatar)
            avatar.textContent = initials;

        if(nameElement)
            nameElement.textContent = name;

        if(idElement)
            idElement.textContent = id;

        if(emailElement)
            emailElement.textContent = email;

        if(departmentElement)
            departmentElement.textContent =
                departmentName(dept);

        if(semesterElement)
            semesterElement.textContent =
                `Semester ${sem}`;

        if(statusElement)
            statusElement.textContent =
                capitalize(statusValue);


        modal.classList.add("show");

        modal.setAttribute(
            "aria-hidden",
            "false"
        );

    }


    function closeStudentModal(){

        if(!modal) return;

        modal.classList.remove("show");

        modal.setAttribute(
            "aria-hidden",
            "true"
        );

    }


    table
        .querySelectorAll(
            ".student-action.view"
        )
        .forEach(button => {

            button.addEventListener(
                "click",
                () => {

                    const row =
                        button.closest("tr");

                    if(row){
                        openStudentModal(row);
                    }

                }
            );

        });


    closeModal?.addEventListener(
        "click",
        closeStudentModal
    );


    modalCloseButton?.addEventListener(
        "click",
        closeStudentModal
    );


    modal
        ?.querySelector(
            ".student-modal-overlay"
        )
        ?.addEventListener(
            "click",
            closeStudentModal
        );


    document.addEventListener(
        "keydown",
        event => {

            if(event.key === "Escape"){

                closeStudentModal();

            }

        }
    );


    /* ==========================================
       DELETE
    ========================================== */

    table
        .querySelectorAll(
            ".student-action.delete"
        )
        .forEach(button => {

            button.addEventListener(
                "click",
                () => {

                    const row =
                        button.closest("tr");

                    const name =
                        row?.dataset.name ||
                        "this student";


                    const confirmed =
                        confirm(
                            `Are you sure you want to delete ${name}?`
                        );


                    if(confirmed && row){

                        row.remove();

                        updateSelectedCount();

                    }

                }
            );

        });


    /* ==========================================
       EDIT
    ========================================== */

    table
        .querySelectorAll(
            ".student-action.edit"
        )
        .forEach(button => {

            button.addEventListener(
                "click",
                () => {

                    const row =
                        button.closest("tr");

                    const name =
                        row?.dataset.name ||
                        "Student";


                    alert(
                        `Edit Student: ${name}\n\nConnect this action to your student edit page or backend.`
                    );

                }
            );

        });


    /* ==========================================
       BULK DELETE
    ========================================== */

    document
        .getElementById("deleteStudents")
        ?.addEventListener(
            "click",
            () => {

                const selected =
                    table.querySelectorAll(
                        ".student-checkbox:checked"
                    );


                if(selected.length === 0){

                    alert(
                        "Please select at least one student."
                    );

                    return;

                }


                const confirmed =
                    confirm(
                        `Delete ${selected.length} selected student(s)?`
                    );


                if(!confirmed) return;


                selected.forEach(
                    checkbox => {

                        const row =
                            checkbox.closest("tr");

                        row?.remove();

                    }
                );


                updateSelectedCount();

            }
        );


    /* ==========================================
       ACTIVATE
    ========================================== */

    document
        .getElementById("activateStudents")
        ?.addEventListener(
            "click",
            () => {

                const selected =
                    table.querySelectorAll(
                        ".student-checkbox:checked"
                    );


                if(selected.length === 0){

                    alert(
                        "Please select at least one student."
                    );

                    return;

                }


                selected.forEach(
                    checkbox => {

                        const row =
                            checkbox.closest("tr");

                        if(!row) return;


                        row.dataset.status =
                            "active";


                        const badge =
                            row.querySelector(
                                ".status-badge"
                            );


                        if(badge){

                            badge.className =
                                "status-badge active";

                            badge.textContent =
                                "Active";

                        }

                    }
                );


                alert(
                    `${selected.length} student(s) activated.`
                );

            }
        );


    /* ==========================================
       EXPORT
    ========================================== */

    document
        .getElementById("exportStudents")
        ?.addEventListener(
            "click",
            () => {

                const headers = [
                    "Name",
                    "Student ID",
                    "Department",
                    "Semester",
                    "Email",
                    "Status"
                ];


                const data =
                    rows
                        .filter(
                            row =>
                                row.style.display !== "none"
                        )
                        .map(row => {

                            return [

                                row.dataset.name,

                                row.dataset.id,

                                row.dataset.department,

                                row.dataset.semester,

                                row.children[5]
                                    ?.textContent
                                    .trim(),

                                row.dataset.status

                            ];

                        });


                let csv =
                    headers.join(",") + "\n";


                data.forEach(row => {

                    csv +=
                        row
                            .map(
                                value =>
                                    `"${String(value)
                                        .replaceAll('"','""')}"`
                            )
                            .join(",")
                        + "\n";

                });


                const blob =
                    new Blob(
                        [csv],
                        {
                            type:
                                "text/csv;charset=utf-8;"
                        }
                    );


                const url =
                    URL.createObjectURL(blob);


                const link =
                    document.createElement("a");


                link.href = url;

                link.download =
                    "studenthub-students.csv";


                link.click();


                URL.revokeObjectURL(url);

            }
        );


    /* ==========================================
       ADD STUDENT
    ========================================== */

    document
        .getElementById("addStudentButton")
        ?.addEventListener(
            "click",
            () => {

                alert(
                    "Add Student form will be connected here."
                );

            }
        );

}


/* ================================================
   HELPERS
================================================ */

function capitalize(value){

    if(!value) return "";

    return value.charAt(0).toUpperCase()
        + value.slice(1);

}


function departmentName(value){

    const names = {

        IT:
            "Information Technology",

        CSE:
            "Computer Science",

        CE:
            "Computer Engineering",

        ECE:
            "Electronics & Communication"

    };

    return names[value] || value;

}


/* ================================================
   START
================================================ */

document.addEventListener(
    "DOMContentLoaded",
    () => {

        initializeStudentManagement();

    }
);
/* =========================================================
   STUDENTHUB — MAIN JAVASCRIPT
   Admin Portal + Theme + Navigation + UI
   ========================================================= */

"use strict";

/* =========================================================
   DOM READY
   ========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    /* Legacy admin bootstrap.
       initTheme() / initLogout() / initForms() were REMOVED: they targeted the
       old admin markup and now clash with the rebuilt admin portal, which is
       fully handled by initializeAdminPortal() in the main program above.
       The remaining calls are harmless no-ops on the current markup. */

    initThemeToggle();
    initMobileSidebar();
    initAdminSearch();
    initAdminButtons();
    initNotifications();
    initCounters();
    initActiveNavigation();
    initFloatingBalls();

});


/* =========================================================
   THEME SYSTEM
   Green = Default
   Blue  = Dark Theme
   ========================================================= */

function initTheme() {

    const savedTheme = localStorage.getItem("studenthub-theme");

    if (savedTheme === "blue") {
        document.body.classList.add("blue-theme");
    }

}


function initThemeToggle() {

    const themeButtons = document.querySelectorAll(
        "#themeToggle, .theme-toggle, [data-theme-toggle]"
    );

    themeButtons.forEach(button => {

        button.addEventListener("click", () => {

            adminLegacyToggleTheme();

        });

    });

}


function adminLegacyToggleTheme() {

    const body = document.body;

    /* Animation */

    body.classList.add("theme-changing");

    setTimeout(() => {

        body.classList.toggle("blue-theme");

        const isBlue =
            body.classList.contains("blue-theme");

        localStorage.setItem(
            "studenthub-theme",
            isBlue ? "blue" : "green"
        );

        updateThemeIcons(isBlue);

    }, 120);

    setTimeout(() => {

        body.classList.remove("theme-changing");

    }, 550);

}


function updateThemeIcons(isBlue) {

    const themeButtons = document.querySelectorAll(
        "#themeToggle, .theme-toggle, [data-theme-toggle]"
    );

    themeButtons.forEach(button => {

        const icon =
            button.querySelector("i, svg");

        if (!icon) return;

        if (isBlue) {

            icon.classList.remove("fa-moon");
            icon.classList.add("fa-sun");

        } else {

            icon.classList.remove("fa-sun");
            icon.classList.add("fa-moon");

        }

    });

}


/* =========================================================
   MOBILE ADMIN SIDEBAR
   ========================================================= */

function initMobileSidebar() {

    const sidebar =
        document.querySelector(".admin-sidebar");

    const toggle =
        document.querySelector(".admin-menu-toggle");

    const overlay =
        document.querySelector(".admin-overlay");

    if (!sidebar || !toggle) return;


    toggle.addEventListener("click", () => {

        sidebar.classList.toggle("open");

        if (overlay) {
            overlay.classList.toggle("active");
        }

    });


    if (overlay) {

        overlay.addEventListener("click", () => {

            closeSidebar();

        });

    }


    const sidebarLinks =
        sidebar.querySelectorAll("a");

    sidebarLinks.forEach(link => {

        link.addEventListener("click", () => {

            if (window.innerWidth <= 850) {

                closeSidebar();

            }

        });

    });


    window.addEventListener("resize", () => {

        if (window.innerWidth > 850) {

            closeSidebar();

        }

    });


    function closeSidebar() {

        sidebar.classList.remove("open");

        if (overlay) {
            overlay.classList.remove("active");
        }

    }

}


/* =========================================================
   ADMIN SEARCH
   ========================================================= */

function initAdminSearch() {

    const searchInputs =
        document.querySelectorAll(
            ".admin-search input, [data-admin-search]"
        );

    searchInputs.forEach(input => {

        input.addEventListener("input", () => {

            const searchValue =
                input.value.toLowerCase().trim();

            searchAdminTable(searchValue);

        });

    });

}


function searchAdminTable(value) {

    const tables =
        document.querySelectorAll(".admin-table");

    tables.forEach(table => {

        const rows =
            table.querySelectorAll(
                "tbody tr"
            );

        rows.forEach(row => {

            const text =
                row.textContent.toLowerCase();

            if (
                value === "" ||
                text.includes(value)
            ) {

                row.style.display = "";

            } else {

                row.style.display = "none";

            }

        });

    });

}


/* =========================================================
   ADMIN BUTTONS
   ========================================================= */

function initAdminButtons() {

    /* Small action buttons */

    const smallButtons =
        document.querySelectorAll(
            ".admin-small-btn"
        );

    smallButtons.forEach(button => {

        button.addEventListener("click", event => {

            event.preventDefault();

            const action =
                button.dataset.action ||
                getButtonAction(button);

            handleAdminAction(
                action,
                button
            );

        });

    });


    /* Admin buttons */

    const adminButtons =
        document.querySelectorAll(
            ".admin-btn[data-action]"
        );

    adminButtons.forEach(button => {

        button.addEventListener("click", event => {

            const action =
                button.dataset.action;

            if (!action) return;

            handleAdminAction(
                action,
                button
            );

        });

    });

}


function getButtonAction(button) {

    const text =
        button.textContent
            .toLowerCase()
            .trim();

    if (text.includes("delete")) {
        return "delete";
    }

    if (text.includes("edit")) {
        return "edit";
    }

    if (text.includes("view")) {
        return "view";
    }

    return "action";

}


function handleAdminAction(action, button) {

    switch (action) {

        case "delete":

            handleDelete(button);

            break;


        case "edit":

            showToast(
                "Edit option selected."
            );

            break;


        case "view":

            showToast(
                "Opening student details..."
            );

            break;


        case "announcement":

            showToast(
                "Announcement published successfully."
            );

            break;


        case "add-student":

            showToast(
                "Add Student form is ready."
            );

            break;


        case "export":

            exportStudentData();

            break;


        default:

            showToast(
                "Action completed successfully."
            );

    }

}


/* =========================================================
   DELETE ACTION
   ========================================================= */

function handleDelete(button) {

    const row =
        button.closest("tr");

    if (!row) return;

    const studentName =
        row.querySelector(
            ".admin-student-info strong"
        );

    const name =
        studentName
            ? studentName.textContent.trim()
            : "this student";


    const confirmed =
        window.confirm(
            `Are you sure you want to delete ${name}?`
        );


    if (!confirmed) return;


    row.style.transition =
        "opacity .3s ease, transform .3s ease";

    row.style.opacity = "0";

    row.style.transform =
        "translateX(20px)";


    setTimeout(() => {

        row.remove();

        showToast(
            `${name} has been removed.`
        );

    }, 300);

}


/* =========================================================
   NOTIFICATIONS
   ========================================================= */

function initNotifications() {

    const notificationButtons =
        document.querySelectorAll(
            "#notificationBtn, .notification-btn, [data-notification]"
        );

    notificationButtons.forEach(button => {

        button.addEventListener("click", () => {

            showToast(
                "You have new StudentHub notifications."
            );

        });

    });

}


/* =========================================================
   TOAST MESSAGE
   ========================================================= */

function showToast(message, type = "success") {

    const oldToast =
        document.querySelector(".admin-toast");

    if (oldToast) {
        oldToast.remove();
    }


    const toast =
        document.createElement("div");

    toast.className =
        "admin-toast";


    let icon = "✓";

    if (type === "error") {
        icon = "!";
    }

    if (type === "info") {
        icon = "i";
    }


    toast.innerHTML = `
        <span class="admin-toast-icon">
            ${icon}
        </span>

        <span>
            ${escapeHTML(message)}
        </span>
    `;


    document.body.appendChild(toast);


    setTimeout(() => {

        toast.style.opacity = "0";
        toast.style.transform =
            "translateY(15px)";

        setTimeout(() => {

            toast.remove();

        }, 300);

    }, 3000);

}


/* =========================================================
   LOGOUT
   ========================================================= */

function initLogout() {

    const logoutButtons =
        document.querySelectorAll(
            "#adminLogout, .admin-logout, [data-logout]"
        );

    logoutButtons.forEach(button => {

        button.addEventListener("click", event => {

            event.preventDefault();

            const confirmed =
                window.confirm(
                    "Are you sure you want to logout?"
                );

            if (!confirmed) return;


            localStorage.removeItem(
                "studenthub-admin-login"
            );


            showToast(
                "Logging out..."
            );


            setTimeout(() => {

                window.location.href =
                    "login.html";

            }, 900);

        });

    });

}


/* =========================================================
   ADMIN LOGIN
   ========================================================= */

function initForms() {

    const loginForm =
        document.querySelector(
            "#adminLoginForm, .admin-login-form"
        );

    if (!loginForm) return;


    loginForm.addEventListener(
        "submit",
        event => {

            event.preventDefault();


            const usernameInput =
                loginForm.querySelector(
                    'input[name="username"], #adminUsername'
                );


            const passwordInput =
                loginForm.querySelector(
                    'input[name="password"], #adminPassword'
                );


            const username =
                usernameInput
                    ? usernameInput.value.trim()
                    : "";


            const password =
                passwordInput
                    ? passwordInput.value
                    : "";


            /*
             * Demo credentials.
             *
             * Change these before using
             * the website in production.
             */

            const ADMIN_USERNAME =
                "admin";

            const ADMIN_PASSWORD =
                "StudentHub@123";


            if (
                username === ADMIN_USERNAME &&
                password === ADMIN_PASSWORD
            ) {

                localStorage.setItem(
                    "studenthub-admin-login",
                    "true"
                );


                showToast(
                    "Admin login successful."
                );


                setTimeout(() => {

                    window.location.href =
                        "admin.html";

                }, 800);

            } else {

                showToast(
                    "Invalid admin username or password.",
                    "error"
                );

            }

        }
    );

}


/* =========================================================
   ADMIN LOGIN PROTECTION
   ========================================================= */

function checkAdminLogin() {

    const isAdminPage =
        document.body.classList.contains(
            "admin-page"
        );


    if (!isAdminPage) return;


    const loggedIn =
        localStorage.getItem(
            "studenthub-admin-login"
        );


    /*
     * Don't redirect the login page itself.
     */

    const isLoginPage =
        document.querySelector(
            ".admin-login-page"
        );


    if (
        !loggedIn &&
        !isLoginPage
    ) {

        window.location.href =
            "login.html";

    }

}


/* =========================================================
   ACTIVE NAVIGATION
   ========================================================= */

function initActiveNavigation() {

    const currentPage =
        window.location.pathname
            .split("/")
            .pop()
            .toLowerCase();


    const links =
        document.querySelectorAll(
            ".admin-nav a"
        );


    links.forEach(link => {

        const href =
            link.getAttribute("href");


        if (!href) return;


        const linkPage =
            href.split("/")
                .pop()
                .split("#")[0]
                .toLowerCase();


        if (
            linkPage &&
            linkPage === currentPage
        ) {

            link.classList.add("active");

        }

    });

}


/* =========================================================
   DASHBOARD COUNTERS
   ========================================================= */

function initCounters() {

    const counters =
        document.querySelectorAll(
            "[data-counter]"
        );


    counters.forEach(counter => {

        const target =
            parseInt(
                counter.dataset.counter,
                10
            );


        if (
            Number.isNaN(target)
        ) {
            return;
        }


        adminLegacyAnimateCounter(
            counter,
            target
        );

    });

}


function adminLegacyAnimateCounter(element, target) {

    let current = 0;

    const duration = 1000;

    const startTime =
        performance.now();


    function updateCounter(currentTime) {

        const elapsed =
            currentTime - startTime;


        const progress =
            Math.min(
                elapsed / duration,
                1
            );


        const ease =
            1 - Math.pow(
                1 - progress,
                3
            );


        current =
            Math.floor(
                target * ease
            );


        element.textContent =
            current.toLocaleString();


        if (progress < 1) {

            requestAnimationFrame(
                updateCounter
            );

        } else {

            element.textContent =
                target.toLocaleString();

        }

    }


    requestAnimationFrame(
        updateCounter
    );

}


/* =========================================================
   FILTERS
   ========================================================= */

function initFilters() {

    const filters =
        document.querySelectorAll(
            ".admin-select"
        );


    filters.forEach(filter => {

        filter.addEventListener(
            "change",
            () => {

                applyAdminFilters();

            }
        );

    });

}


function applyAdminFilters() {

    const statusFilter =
        document.querySelector(
            "#statusFilter"
        );


    if (!statusFilter) return;


    const selected =
        statusFilter.value
            .toLowerCase();


    const rows =
        document.querySelectorAll(
            ".admin-table tbody tr"
        );


    rows.forEach(row => {

        if (
            selected === "" ||
            selected === "all"
        ) {

            row.style.display = "";

            return;

        }


        const status =
            row.querySelector(
                ".admin-status"
            );


        if (!status) return;


        const statusText =
            status.textContent
                .toLowerCase()
                .trim();


        row.style.display =
            statusText.includes(selected)
                ? ""
                : "none";

    });

}


/* =========================================================
   EXPORT STUDENT DATA
   ========================================================= */

function exportStudentData() {

    const table =
        document.querySelector(
            ".admin-table"
        );


    if (!table) {

        showToast(
            "No student data available.",
            "error"
        );

        return;

    }


    const rows =
        table.querySelectorAll(
            "tr"
        );


    const csvData = [];


    rows.forEach(row => {

        const cells =
            row.querySelectorAll(
                "th, td"
            );


        const rowData = [];


        cells.forEach(cell => {

            let value =
                cell.textContent
                    .replace(/\s+/g, " ")
                    .trim();


            value =
                `"${value.replace(
                    /"/g,
                    '""'
                )}"`;


            rowData.push(value);

        });


        csvData.push(
            rowData.join(",")
        );

    });


    const csv =
        csvData.join("\n");


    const blob =
        new Blob(
            [csv],
            {
                type: "text/csv;charset=utf-8;"
            }
        );


    const url =
        URL.createObjectURL(blob);


    const link =
        document.createElement("a");


    link.href = url;

    link.download =
        "studenthub-students.csv";


    document.body.appendChild(link);

    link.click();

    link.remove();


    URL.revokeObjectURL(url);


    showToast(
        "Student data exported successfully."
    );

}


/* =========================================================
   FLOATING BACKGROUND BALLS
   ========================================================= */

function initFloatingBalls() {

    const container =
        document.querySelector(
            ".floating-balls, .background-balls"
        );


    if (!container) return;


    const existingBalls =
        container.querySelectorAll(
            ".ball"
        );


    if (existingBalls.length > 0) {
        return;
    }


    const totalBalls =
        window.innerWidth <= 600
            ? 8
            : 14;


    for (
        let i = 0;
        i < totalBalls;
        i++
    ) {

        const ball =
            document.createElement("span");


        ball.className = "ball";


        const size =
            Math.floor(
                Math.random() * 45
            ) + 15;


        ball.style.width =
            `${size}px`;

        ball.style.height =
            `${size}px`;


        ball.style.left =
            `${Math.random() * 100}%`;


        ball.style.top =
            `${Math.random() * 100}%`;


        ball.style.animationDelay =
            `${Math.random() * 5}s`;


        ball.style.animationDuration =
            `${5 + Math.random() * 8}s`;


        container.appendChild(ball);

    }

}


/* =========================================================
   PASSWORD SHOW / HIDE
   ========================================================= */

function initPasswordToggle() {

    const toggleButtons =
        document.querySelectorAll(
            "[data-password-toggle]"
        );


    toggleButtons.forEach(button => {

        button.addEventListener(
            "click",
            () => {

                const targetID =
                    button.dataset
                        .passwordToggle;


                const input =
                    document.getElementById(
                        targetID
                    );


                if (!input) return;


                if (
                    input.type === "password"
                ) {

                    input.type = "text";

                    button.textContent =
                        "Hide";

                } else {

                    input.type = "password";

                    button.textContent =
                        "Show";

                }

            }
        );

    });

}


/* =========================================================
   ESCAPE HTML
   ========================================================= */

function escapeHTML(value) {

    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

}


/* =========================================================
   SMOOTH SCROLL
   ========================================================= */

function initSmoothScroll() {

    const links =
        document.querySelectorAll(
            'a[href^="#"]'
        );


    links.forEach(link => {

        link.addEventListener(
            "click",
            event => {

                const id =
                    link.getAttribute(
                        "href"
                    );


                if (
                    !id ||
                    id === "#"
                ) {
                    return;
                }


                const target =
                    document.querySelector(
                        id
                    );


                if (!target) return;


                event.preventDefault();


                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

            }
        );

    });

}


/* =========================================================
   INITIALIZE EXTRA FEATURES
   ========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    () => {

        checkAdminLogin();

        initFilters();

        initPasswordToggle();

        initSmoothScroll();

    }
);


/* =========================================================
   GLOBAL FUNCTIONS
   These can be called from HTML onclick=""
   ========================================================= */

window.StudentHub = {

    changeTheme,

    showToast,

    exportStudentData,

    checkAdminLogin

};

/* =========================================================
   PASSWORD VISIBILITY TOGGLE
   (no-op on pages without .password-toggle)
========================================================= */

function initializePasswordToggles() {

    const toggles =
        document.querySelectorAll(".password-toggle");

    if (!toggles.length) return;

    toggles.forEach(toggle => {

        toggle.addEventListener("click", () => {

            const targetId = toggle.dataset.target;
            if (!targetId) return;

            const input = document.getElementById(targetId);
            if (!input) return;

            const icon = toggle.querySelector("i");
            const isHidden = input.type === "password";

            input.type = isHidden ? "text" : "password";

            toggle.setAttribute(
                "aria-label",
                isHidden ? "Hide password" : "Show password"
            );

            if (icon) {
                icon.classList.toggle("fa-eye", !isHidden);
                icon.classList.toggle("fa-eye-slash", isHidden);
            }

        });

    });

}


/* =========================================================
   AUTH FORMS  (login / register)
   Client-side validation + feedback for the demo portal.
   (all no-op on pages without the matching markup)
========================================================= */

function initializeAuthForms() {

    initializePasswordStrength();
    initializeLoginForm();
    initializeRegisterForm();

}


function setFormMessage(element, type, text) {

    if (!element) return;

    element.className = "form-message show " + type;

    const icon =
        type === "success"
            ? "fa-circle-check"
            : "fa-circle-exclamation";

    element.innerHTML =
        '<i class="fa-solid ' + icon + '"></i><span>' + text + "</span>";

}


function scorePassword(value) {

    let score = 0;

    if (!value) return 0;

    if (value.length >= 8) score++;
    if (value.length >= 12) score++;
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
    if (/\d/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value)) score++;

    return score;

}


function initializePasswordStrength() {

    const input =
        document.getElementById("registerPassword");

    const meter =
        document.getElementById("passwordStrength");

    if (!input || !meter) return;

    const label =
        meter.querySelector(".password-strength-label");

    input.addEventListener("input", () => {

        const value = input.value;

        meter.classList.remove(
            "is-weak",
            "is-fair",
            "is-strong"
        );

        if (!value) {
            meter.classList.remove("show");
            if (label) {
                label.textContent =
                    "Use 8+ characters with letters, numbers & symbols.";
            }
            return;
        }

        meter.classList.add("show");

        const score = scorePassword(value);

        if (score <= 2) {
            meter.classList.add("is-weak");
            if (label) label.textContent = "Weak password";
        } else if (score <= 3) {
            meter.classList.add("is-fair");
            if (label) label.textContent = "Fair password";
        } else {
            meter.classList.add("is-strong");
            if (label) label.textContent = "Strong password";
        }

    });

}


function initializeLoginForm() {

    const form =
        document.getElementById("studentLoginForm");

    if (!form) return;

    const message =
        document.getElementById("loginMessage");

    const button =
        form.querySelector("[type='submit']");

    form.addEventListener("submit", event => {

        event.preventDefault();

        const idField =
            document.getElementById("studentId");

        const pwField =
            document.getElementById("studentPassword");

        const id = idField ? idField.value.trim() : "";
        const password = pwField ? pwField.value : "";

        if (!id || !password) {
            setFormMessage(
                message,
                "error",
                "Please enter your Student ID or email and your password."
            );
            return;
        }

        const result = StudentHubDB.authenticate(id, password);

        if (!result.ok) {
            setFormMessage(message, "error", result.error);
            return;
        }

        StudentHubDB.setSession(result.user.id);

        StudentHubDB.pushNotification({
            type: "login",
            title: "Student signed in",
            text:
                result.user.name +
                " (" +
                result.user.id +
                ") just signed in."
        });

        setFormMessage(
            message,
            "success",
            "Welcome back, " +
                shFirstName(result.user.name) +
                "! Redirecting to your dashboard..."
        );

        if (button) {
            button.disabled = true;
            button.classList.add("is-loading");
        }

        setTimeout(() => {
            window.location.href = "dashboard.html";
        }, 1000);

    });

}


function initializeRegisterForm() {

    const form =
        document.getElementById("studentRegisterForm");

    if (!form) return;

    const message =
        document.getElementById("registerMessage");

    const button =
        form.querySelector("[type='submit']");

    form.addEventListener("submit", event => {

        event.preventDefault();

        const nameField =
            document.getElementById("studentName");

        const idField =
            document.getElementById("registerStudentId");

        const emailField =
            document.getElementById("studentEmail");

        const pwField =
            document.getElementById("registerPassword");

        const confirmField =
            document.getElementById("confirmPassword");

        const terms =
            document.getElementById("terms");

        const semesterField =
            document.getElementById("semester");

        const name = nameField ? nameField.value.trim() : "";
        const studentId = idField ? idField.value.trim() : "";
        const email = emailField ? emailField.value.trim() : "";
        const password = pwField ? pwField.value : "";
        const confirm = confirmField ? confirmField.value : "";

        if (!name || !studentId || !email) {
            setFormMessage(
                message,
                "error",
                "Please fill in your name, Student ID and email."
            );
            return;
        }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            setFormMessage(
                message,
                "error",
                "Please enter a valid email address."
            );
            return;
        }

        if (password.length < 8) {
            setFormMessage(
                message,
                "error",
                "Password must be at least 8 characters."
            );
            return;
        }

        if (password !== confirm) {
            setFormMessage(
                message,
                "error",
                "Passwords do not match. Please re-enter them."
            );
            return;
        }

        if (terms && !terms.checked) {
            setFormMessage(
                message,
                "error",
                "Please accept the terms to continue."
            );
            return;
        }

        if (button) {
            button.disabled = true;
            button.classList.add("is-loading");
        }

        const formData = new FormData();
        formData.append("name", name);
        formData.append("studentId", studentId);
        formData.append("email", email);
        formData.append("password", password);
        formData.append("confirmPassword", confirm);
        if (semesterField && semesterField.value) {
            formData.append("semester", semesterField.value);
        }

        fetch("../php/process_registration.php", {
            method: "POST",
            body: formData,
            headers: {
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(response => {
            return response.json().catch(() => {
                throw new Error("Unable to parse server response.");
            });
        })
        .then(data => {
            if (!data.ok && !data.success) {
                setFormMessage(message, "error", data.error || data.message || "Registration failed.");
                if (button) {
                    button.disabled = false;
                    button.classList.remove("is-loading");
                }
                return;
            }

            StudentHubDB.registerUser({
                name: name,
                id: studentId,
                email: email,
                password: password,
                semester: semesterField && semesterField.value
                    ? semesterField.value
                    : null
            });

            StudentHubDB.setSession(studentId);

            setFormMessage(
                message,
                "success",
                data.message || "Account created! Let's set up your profile..."
            );

            setTimeout(() => {
                window.location.href = "profile.html";
            }, 1400);
        })
        .catch(() => {
            // Fallback for file:/// protocol without running web server
            const result = StudentHubDB.registerUser({
                name: name,
                id: studentId,
                email: email,
                password: password,
                semester: semesterField && semesterField.value
                    ? semesterField.value
                    : null
            });

            if (!result.ok) {
                setFormMessage(message, "error", result.error);
                if (button) {
                    button.disabled = false;
                    button.classList.remove("is-loading");
                }
                return;
            }

            StudentHubDB.setSession(result.user.id);

            setFormMessage(
                message,
                "success",
                "Account created! Let's set up your profile..."
            );

            setTimeout(() => {
                window.location.href = "profile.html";
            }, 1400);
        });

    });

}


/* =========================================================
   PROFILE — edit personal information (demo, client-side)
   Self-guarding: no-op unless #profileForm exists.
========================================================= */

function initializeProfileForm() {

    const form =
        document.getElementById("profileForm");

    if (!form) return;


    /* profile.html is session-guarded: no session -> back to login */
    const currentUser = StudentHubDB.getSessionUser();

    if (!currentUser) {
        window.location.href = "login.html";
        return;
    }

    shApplyUser(currentUser);
    shBindLogout();


    const message =
        document.getElementById("profileFormMessage");

    const emailPattern =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


    form.addEventListener("submit", event => {

        event.preventDefault();


        const firstName =
            form.querySelector("#firstName");

        const lastName =
            form.querySelector("#lastName");

        const email =
            form.querySelector("#profileEmail");

        const phone =
            form.querySelector("#profilePhone");

        const dob =
            form.querySelector("#profileDob");

        const gender =
            form.querySelector("#profileGender");

        const address =
            form.querySelector("#profileAddress");

        const bio =
            form.querySelector("#profileBioField");


        const first = firstName ? firstName.value.trim() : "";
        const last = lastName ? lastName.value.trim() : "";

        if (!first || !last) {

            setFormMessage(
                message,
                "error",
                "Please enter both your first and last name."
            );

            return;

        }


        const emailValue = email ? email.value.trim() : "";

        if (!emailPattern.test(emailValue)) {

            setFormMessage(
                message,
                "error",
                "Please enter a valid email address."
            );

            return;

        }


        /* an email may only belong to one account */
        const owner = StudentHubDB.findByEmail(emailValue);

        if (owner && owner.id !== currentUser.id) {

            setFormMessage(
                message,
                "error",
                "That email is already used by another account."
            );

            return;

        }


        const result = StudentHubDB.updateUser(currentUser.id, {
            name: (first + " " + last).trim(),
            email: emailValue,
            phone: phone ? phone.value.trim() : "",
            dob: dob ? dob.value.trim() : "",
            gender: gender ? gender.value.trim() : "",
            address: address ? address.value.trim() : "",
            bio: bio ? bio.value.trim() : ""
        });

        if (!result.ok) {
            setFormMessage(message, "error", result.error);
            return;
        }

        shApplyUser(result.user);

        setFormMessage(
            message,
            "success",
            "Your profile has been updated successfully."
        );

    });


    form.addEventListener("reset", () => {

        if (message) {
            message.className = "form-message";
            message.innerHTML = "";
        }

        /* re-fill fields with the saved values after native reset */
        setTimeout(() => {
            const fresh = StudentHubDB.getSessionUser();
            if (fresh) shApplyUser(fresh);
        }, 0);

    });

}


/* =========================================================
   EVENTS — category filter
   Self-guarding: no-op unless .events-filter exists.
========================================================= */

function initializeEventFilters() {

    const filter =
        document.querySelector(".events-filter");

    if (!filter) return;


    const buttons =
        filter.querySelectorAll(".events-filter-btn");

    const cards =
        document.querySelectorAll(
            ".events-collection .event-card"
        );

    const empty =
        document.getElementById("eventsEmpty");


    const applyFilter = category => {

        let visible = 0;

        cards.forEach(card => {

            const match =
                category === "all" ||
                card.dataset.category === category;

            card.classList.toggle("is-hidden", !match);

            if (match) visible++;

        });

        if (empty) {

            empty.hidden = visible !== 0;

        }

    };


    buttons.forEach(button => {

        button.addEventListener("click", () => {

            buttons.forEach(b =>
                b.classList.remove("active")
            );

            button.classList.add("active");

            applyFilter(
                button.dataset.filter || "all"
            );

        });

    });

}


/* =========================================================
   FAQ — accordion + live search
   Self-guarding: no-op unless .faq-groups exists.
========================================================= */

function initializeFaq() {

    const groupsRoot =
        document.querySelector(".faq-groups");

    if (!groupsRoot) return;


    const items =
        Array.from(
            document.querySelectorAll(".faq-item")
        );

    const groups =
        Array.from(
            document.querySelectorAll(".faq-group")
        );

    const search =
        document.getElementById("faqSearch");

    const empty =
        document.getElementById("faqEmpty");


    /* Accordion toggling */

    items.forEach(item => {

        const question =
            item.querySelector(".faq-question");

        if (!question) return;

        question.addEventListener("click", () => {

            const isOpen =
                item.classList.toggle("is-open");

            question.setAttribute(
                "aria-expanded",
                isOpen ? "true" : "false"
            );

        });

    });


    /* Live search */

    if (!search) return;

    search.addEventListener("input", () => {

        const term =
            search.value.trim().toLowerCase();

        let visible = 0;

        items.forEach(item => {

            const match =
                !term ||
                item.textContent
                    .toLowerCase()
                    .includes(term);

            item.classList.toggle("is-hidden", !match);

            if (match) visible++;

        });


        groups.forEach(group => {

            const hasVisible =
                group.querySelector(
                    ".faq-item:not(.is-hidden)"
                );

            group.classList.toggle("is-hidden", !hasVisible);

        });


        if (empty) {

            empty.hidden = visible !== 0;

        }

    });

}


/* =========================================================
   FEEDBACK — star rating + validated submit (demo)
   Self-guarding: no-op unless #feedbackForm exists.
========================================================= */

function initializeFeedbackForm() {

    const form =
        document.getElementById("feedbackForm");

    if (!form) return;


    const ratingBox =
        document.getElementById("feedbackRating");

    const stars =
        ratingBox
            ? Array.from(
                  ratingBox.querySelectorAll(".rating-star")
              )
            : [];

    const caption =
        document.getElementById("ratingCaption");

    const message =
        document.getElementById("feedbackFormMessage");

    const captions = [
        "Tap a star to rate",
        "Poor",
        "Fair",
        "Good",
        "Very good",
        "Excellent"
    ];

    const emailPattern =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


    const paint = (count, cls) =>
        stars.forEach((star, index) =>
            star.classList.toggle(cls, index < count)
        );


    const setRating = count => {

        if (ratingBox) {
            ratingBox.dataset.value = String(count);
            ratingBox.classList.toggle("is-set", count > 0);
        }

        paint(count, "is-active");

        if (caption) {
            caption.textContent =
                captions[count] || captions[0];
        }

    };


    stars.forEach(star => {

        const value =
            parseInt(star.dataset.value, 10);

        star.addEventListener("click", () =>
            setRating(value)
        );

        star.addEventListener("mouseenter", () =>
            paint(value, "is-hover")
        );

        star.addEventListener("mouseleave", () =>
            stars.forEach(s =>
                s.classList.remove("is-hover")
            )
        );

    });


    form.addEventListener("submit", event => {

        event.preventDefault();


        const rating =
            ratingBox
                ? parseInt(ratingBox.dataset.value, 10) || 0
                : 0;

        const name =
            form.querySelector("#feedbackName").value.trim();

        const email =
            form.querySelector("#feedbackEmail").value.trim();

        const subject =
            form.querySelector("#feedbackSubject").value.trim();

        const body =
            form.querySelector("#feedbackMessage").value.trim();


        if (rating < 1) {
            setFormMessage(message, "error", "Please select a star rating.");
            return;
        }

        if (!name || !subject || !body) {
            setFormMessage(message, "error", "Please fill in all required fields.");
            return;
        }

        if (!emailPattern.test(email)) {
            setFormMessage(message, "error", "Please enter a valid email address.");
            return;
        }


        form.reset();

        setFormMessage(
            message,
            "success",
            "Thank you! Your feedback has been submitted."
        );

    });


    form.addEventListener("reset", () => {

        setRating(0);

        if (message) {
            message.className = "form-message";
            message.innerHTML = "";
        }

    });

}


/* =========================================================
   CONTACT — validated message form (demo)
   Self-guarding: no-op unless #contactForm exists.
========================================================= */

function initializeContactForm() {

    const form =
        document.getElementById("contactForm");

    if (!form) return;


    const message =
        document.getElementById("contactFormMessage");

    const emailPattern =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


    form.addEventListener("submit", event => {

        event.preventDefault();


        const name =
            form.querySelector("#contactName").value.trim();

        const email =
            form.querySelector("#contactEmail").value.trim();

        const subject =
            form.querySelector("#contactSubject").value.trim();

        const body =
            form.querySelector("#contactMessage").value.trim();


        if (!name) {
            setFormMessage(message, "error", "Please enter your name.");
            return;
        }

        if (!emailPattern.test(email)) {
            setFormMessage(message, "error", "Please enter a valid email address.");
            return;
        }

        if (!subject) {
            setFormMessage(message, "error", "Please choose a subject.");
            return;
        }

        if (!body) {
            setFormMessage(message, "error", "Please enter your message.");
            return;
        }


        form.reset();

        setFormMessage(
            message,
            "success",
            "Thanks for reaching out! We'll reply within two working days."
        );

    });


    form.addEventListener("reset", () => {

        if (message) {
            message.className = "form-message";
            message.innerHTML = "";
        }

    });

}


/* =========================================================
   =========================================================
   STUDENTHUB DATA LAYER  (multi-user, client-side)
   Real per-user accounts, sessions, admin control.
   Storage: localStorage (serverless static site).
   =========================================================
========================================================= */

const StudentHubDB = (function () {

    const USERS_KEY = "studenthub-users";
    const SESSION_KEY = "studenthub-session";
    const ADMIN_KEY = "studenthub-admin-login";
    const NOTIF_KEY = "studenthub-admin-notifications";

    const DEPT_MAP = {
        IT: "Information Technology",
        CE: "Computer Engineering",
        CO: "Computer Engineering",
        CS: "Computer Science & Engineering",
        EC: "Electronics & Communication",
        EE: "Electrical Engineering",
        ME: "Mechanical Engineering",
        CL: "Civil Engineering",
        CV: "Civil Engineering",
        CH: "Chemical Engineering",
        AI: "AI & Machine Learning"
    };

    const ID_PATTERN = /^\d{2}[A-Za-z]{2}\d{3}$/;
    const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function read(key, fallback) {
        try {
            const raw = localStorage.getItem(key);
            if (raw === null || raw === undefined) return fallback;
            return JSON.parse(raw);
        } catch (err) {
            return fallback;
        }
    }

    function write(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (err) {
            return false;
        }
    }

    function normalizeId(id) {
        return String(id || "").trim().toUpperCase();
    }

    function departmentFromId(id) {
        const clean = normalizeId(id);
        const match = clean.match(/^\d{2}([A-Za-z]{2})/);
        if (!match) return "";
        return DEPT_MAP[match[1].toUpperCase()] || "";
    }

    function batchFromId(id) {
        const clean = normalizeId(id);
        const match = clean.match(/^(\d{2})/);
        if (!match) return "";
        return "20" + match[1];
    }

    function getUsers() {
        const users = read(USERS_KEY, []);
        return Array.isArray(users) ? users : [];
    }

    function saveUsers(users) {
        return write(USERS_KEY, Array.isArray(users) ? users : []);
    }

    function findById(id) {
        const clean = normalizeId(id);
        return getUsers().find(u => u.id === clean) || null;
    }

    function findByEmail(email) {
        const clean = String(email || "").trim().toLowerCase();
        return getUsers().find(
            u => String(u.email || "").trim().toLowerCase() === clean
        ) || null;
    }

    function registerUser(input) {
        input = input || {};

        const id = normalizeId(input.id);
        const name = String(input.name || "").trim();
        const email = String(input.email || "").trim();
        const password = String(input.password || "");

        if (!name) {
            return { ok: false, error: "Please enter the student's full name." };
        }

        if (!ID_PATTERN.test(id)) {
            return {
                ok: false,
                error: "Enter a valid Student ID (e.g. 25IT057)."
            };
        }

        if (!EMAIL_PATTERN.test(email)) {
            return { ok: false, error: "Please enter a valid email address." };
        }

        if (password.length < 6) {
            return {
                ok: false,
                error: "Password must be at least 6 characters."
            };
        }

        if (findById(id)) {
            return {
                ok: false,
                error: "A student with this ID already exists."
            };
        }

        if (findByEmail(email)) {
            return {
                ok: false,
                error: "This email address is already registered."
            };
        }

        const department =
            String(input.department || "").trim() || departmentFromId(id);

        const user = {
            id: id,
            name: name,
            email: email,
            password: password,
            department: department,
            program: department ? "B.Tech " + department : "",
            semester: input.semester != null ? input.semester : null,
            batch: batchFromId(id),
            advisor: "",
            phone: "",
            dob: "",
            gender: "",
            address: "",
            bio: "",
            attendance: null,
            cgpa: null,
            credits: null,
            rank: "",
            status: "active",
            subjects: [],
            createdAt: new Date().toISOString()
        };

        const users = getUsers();
        users.push(user);
        saveUsers(users);

        pushNotification({
            type: "registration",
            title: "New student registered",
            text: name + " (" + id + ") created an account."
        });

        return { ok: true, user: user };
    }

    function authenticate(identifier, password) {
        const value = String(identifier || "").trim();
        const pw = String(password || "");

        if (!value || !pw) {
            return { ok: false, error: "Please enter your credentials." };
        }

        const user = value.indexOf("@") > -1
            ? findByEmail(value)
            : findById(value);

        if (!user || user.password !== pw) {
            return {
                ok: false,
                error: "Incorrect ID/email or password. Please try again."
            };
        }

        return { ok: true, user: user };
    }

    function updateUser(id, patch) {
        const clean = normalizeId(id);
        const users = getUsers();
        const index = users.findIndex(u => u.id === clean);

        if (index === -1) return { ok: false, error: "Student not found." };

        users[index] = Object.assign({}, users[index], patch || {});
        users[index].id = clean;
        saveUsers(users);

        return { ok: true, user: users[index] };
    }

    function removeUser(id) {
        const clean = normalizeId(id);
        const users = getUsers().filter(u => u.id !== clean);
        saveUsers(users);

        if (getSession() === clean) {
            clearSession();
        }

        return { ok: true };
    }

    function getSession() {
        const value = read(SESSION_KEY, null);
        return value ? normalizeId(value) : null;
    }

    function setSession(id) {
        return write(SESSION_KEY, normalizeId(id));
    }

    function clearSession() {
        try {
            localStorage.removeItem(SESSION_KEY);
        } catch (err) {
            /* ignore */
        }
    }

    function getSessionUser() {
        const id = getSession();
        if (!id) return null;

        const user = findById(id);
        if (!user) {
            clearSession();
            return null;
        }

        return user;
    }

    function isAdminLoggedIn() {
        return read(ADMIN_KEY, false) === true;
    }

    function setAdminLoggedIn(value) {
        if (value) {
            return write(ADMIN_KEY, true);
        }
        try {
            localStorage.removeItem(ADMIN_KEY);
        } catch (err) {
            /* ignore */
        }
        return true;
    }

    function getNotifications() {
        const list = read(NOTIF_KEY, []);
        return Array.isArray(list) ? list : [];
    }

    function pushNotification(note) {
        const list = getNotifications();

        list.unshift({
            id: "n" + Date.now() + Math.floor(Math.random() * 1000),
            type: (note && note.type) || "info",
            title: (note && note.title) || "Notification",
            text: (note && note.text) || "",
            time: new Date().toISOString(),
            read: false
        });

        write(NOTIF_KEY, list.slice(0, 50));
    }

    function markNotificationsRead() {
        const list = getNotifications().map(n =>
            Object.assign({}, n, { read: true })
        );
        write(NOTIF_KEY, list);
    }

    function clearNotifications() {
        write(NOTIF_KEY, []);
    }

    function unreadCount() {
        return getNotifications().filter(n => !n.read).length;
    }

    return {
        USERS_KEY: USERS_KEY,
        SESSION_KEY: SESSION_KEY,
        ADMIN_KEY: ADMIN_KEY,
        NOTIF_KEY: NOTIF_KEY,
        departmentFromId: departmentFromId,
        batchFromId: batchFromId,
        normalizeId: normalizeId,
        getUsers: getUsers,
        saveUsers: saveUsers,
        findById: findById,
        findByEmail: findByEmail,
        registerUser: registerUser,
        authenticate: authenticate,
        updateUser: updateUser,
        removeUser: removeUser,
        getSession: getSession,
        setSession: setSession,
        clearSession: clearSession,
        getSessionUser: getSessionUser,
        isAdminLoggedIn: isAdminLoggedIn,
        setAdminLoggedIn: setAdminLoggedIn,
        getNotifications: getNotifications,
        pushNotification: pushNotification,
        markNotificationsRead: markNotificationsRead,
        clearNotifications: clearNotifications,
        unreadCount: unreadCount
    };

})();

if (typeof window !== "undefined") {
    window.StudentHubDB = StudentHubDB;
}


/* =========================================================
   VIEW HELPERS — turn a user record into display values
========================================================= */

const SH_EMPTY = "—"; /* em dash */

function shEscape(value) {
    return String(value == null ? "" : value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function shClamp(value) {
    const num = parseFloat(value);
    if (isNaN(num)) return 0;
    return Math.max(0, Math.min(100, Math.round(num)));
}

function shFirstName(name) {
    const parts = String(name || "").trim().split(/\s+/);
    return parts[0] || "";
}

function shLastName(name) {
    const parts = String(name || "").trim().split(/\s+/);
    return parts.length > 1 ? parts.slice(1).join(" ") : "";
}

function shUserInitials(name) {
    const parts = String(name || "").trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return "ST";
    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function shGreetingWord() {
    const hour = new Date().getHours();
    if (hour < 12) return "Good morning";
    if (hour < 17) return "Good afternoon";
    return "Good evening";
}

function shProfileStrength(user) {
    if (!user) return 0;
    const fields = [
        user.name,
        user.email,
        user.phone,
        user.dob,
        user.gender,
        user.address,
        user.bio
    ];
    const filled = fields.filter(v => String(v || "").trim() !== "").length;
    return Math.round((filled / fields.length) * 100);
}

function shPercentFor(user, key) {
    if (!user) return 0;

    if (key === "attendance") {
        return user.attendance == null ? 0 : shClamp(user.attendance);
    }
    if (key === "cgpa") {
        return user.cgpa == null ? 0 : shClamp((user.cgpa / 10) * 100);
    }
    if (key === "credits") {
        return user.credits == null ? 0 : shClamp((user.credits / 160) * 100);
    }
    if (key === "strength") {
        return shProfileStrength(user);
    }
    return 0;
}

function shRawValue(user, key) {
    if (!user) return "";
    const map = {
        firstName: shFirstName(user.name),
        lastName: shLastName(user.name),
        name: user.name || "",
        email: user.email || "",
        phone: user.phone || "",
        dob: user.dob || "",
        gender: user.gender || "",
        address: user.address || "",
        bio: user.bio || "",
        semester: user.semester != null ? String(user.semester) : "",
        advisor: user.advisor || ""
    };
    return key in map ? map[key] : "";
}

function shDisplayMap(user) {
    if (!user) return {};

    const first = shFirstName(user.name);
    const dept = user.department || "";
    const program = user.program || (dept ? "B.Tech " + dept : "");

    return {
        name: user.name || SH_EMPTY,
        firstName: first || SH_EMPTY,
        lastName: shLastName(user.name) || SH_EMPTY,
        greetingName: first || "there",
        greeting: shGreetingWord(),
        initials: shUserInitials(user.name),
        id: user.id || SH_EMPTY,
        email: user.email || SH_EMPTY,
        phone: user.phone || SH_EMPTY,
        department: dept || SH_EMPTY,
        program: program || SH_EMPTY,
        semester:
            user.semester != null && user.semester !== ""
                ? "Semester " + user.semester
                : SH_EMPTY,
        semesterNum:
            user.semester != null && user.semester !== ""
                ? String(user.semester)
                : SH_EMPTY,
        batch: user.batch ? user.batch + " Batch" : SH_EMPTY,
        advisor: user.advisor || SH_EMPTY,
        dob: user.dob || SH_EMPTY,
        gender: user.gender || SH_EMPTY,
        address: user.address || SH_EMPTY,
        bio:
            user.bio ||
            "Add a short bio from your profile page to tell people about yourself.",
        roleLine:
            (program || "Student") + " · " + (user.id || ""),
        attendance:
            user.attendance == null ? SH_EMPTY : shClamp(user.attendance) + "%",
        attendanceNum:
            user.attendance == null ? SH_EMPTY : String(shClamp(user.attendance)),
        cgpa: user.cgpa == null ? SH_EMPTY : Number(user.cgpa).toFixed(2),
        credits: user.credits == null ? SH_EMPTY : String(user.credits),
        rank: user.rank ? String(user.rank) : SH_EMPTY,
        status: user.status
            ? user.status.charAt(0).toUpperCase() + user.status.slice(1)
            : SH_EMPTY,
        strength: shProfileStrength(user) + "%",
        subjectCount: String(
            Array.isArray(user.subjects) ? user.subjects.length : 0
        )
    };
}


/* =========================================================
   VIEW HELPERS — apply user data to the DOM
========================================================= */

function shApplyProgress(element) {
    if (!element) return;

    const raw = parseFloat(element.dataset.progress) || 0;
    const value = Math.max(0, Math.min(100, raw));

    requestAnimationFrame(() => {
        if (element.classList.contains("dash-ring")) {
            element.style.setProperty("--dash-ring-val", value);
        } else {
            element.style.width = value + "%";
        }
    });
}

function renderUserSubjects(container, user) {
    if (!container) return;

    const subjects =
        user && Array.isArray(user.subjects) ? user.subjects : [];

    container.innerHTML = "";

    if (!subjects.length) {
        const empty = document.createElement("p");
        empty.className = "dash-empty";
        empty.textContent =
            "No subjects yet. Your faculty advisor will add them soon.";
        container.appendChild(empty);
        return;
    }

    subjects.forEach(sub => {
        const pct = shClamp(sub.attendance);

        const row = document.createElement("div");
        row.className = "dash-subject";
        row.innerHTML =
            '<div class="dash-subject-row">' +
                '<span class="dash-subject-name">' +
                    shEscape(sub.name) +
                "</span>" +
                '<span class="dash-subject-val">' + pct + "%</span>" +
            "</div>" +
            '<div class="dash-bar"><span class="dash-bar-fill"></span></div>';

        container.appendChild(row);

        const fill = row.querySelector(".dash-bar-fill");
        if (fill) {
            requestAnimationFrame(() => {
                fill.style.width = pct + "%";
            });
        }
    });
}

function shApplyUser(user) {
    if (!user) return;

    const display = shDisplayMap(user);

    document.querySelectorAll("[data-user]").forEach(el => {
        const key = el.dataset.user;
        if (key && key in display) {
            el.textContent = display[key];
        }
    });

    document.querySelectorAll("[data-user-input]").forEach(el => {
        const key = el.dataset.userInput;
        el.value = shRawValue(user, key);
    });

    document.querySelectorAll("[data-user-ring]").forEach(el => {
        el.dataset.progress = shPercentFor(user, el.dataset.userRing);
        shApplyProgress(el);
    });

    document.querySelectorAll("[data-user-bar]").forEach(el => {
        el.dataset.progress = shPercentFor(user, el.dataset.userBar);
        shApplyProgress(el);
    });

    document.querySelectorAll("[data-user-strength]").forEach(el => {
        el.dataset.progress = shPercentFor(user, "strength");
        shApplyProgress(el);
    });

    document
        .querySelectorAll("[data-user-list='subjects']")
        .forEach(el => renderUserSubjects(el, user));
}

function shBindLogout() {
    document.querySelectorAll("[data-logout]").forEach(btn => {
        if (btn.dataset.logoutBound === "true") return;
        btn.dataset.logoutBound = "true";

        btn.addEventListener("click", event => {
            event.preventDefault();
            StudentHubDB.clearSession();
            window.location.href = "login.html";
        });
    });
}


/* =========================================================
   DASHBOARD — per-user, session guarded
   Self-guarding: only runs on .dashboard-page.
========================================================= */

function initializeDashboard() {
    if (!document.body.classList.contains("dashboard-page")) return;

    const user = StudentHubDB.getSessionUser();

    if (!user) {
        window.location.href = "login.html";
        return;
    }

    shApplyUser(user);
    shBindLogout();
}


/* =========================================================
   ADMIN PORTAL — login gate + real student management
   Self-guarding: only runs when the admin shell exists.
   Uses NEW selectors only (avoids legacy block-2 bindings).
========================================================= */

function initializeAdminPortal() {

    const loginScreen = document.getElementById("adminLoginScreen");
    const portal = document.getElementById("adminPortal");

    if (!loginScreen && !portal) return;

    const ADMIN_USER = "admin";
    const ADMIN_PASS = "StudentHub@123";

    /* ---- toast ---- */
    function adminToast(text, type) {
        const toast = document.getElementById("adminToast");
        if (!toast) return;

        toast.textContent = text;
        toast.className =
            "admin-toast-box show " + (type ? "admin-toast-" + type : "");

        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.className = "admin-toast-box";
        }, 3200);
    }

    /* ---- stats ---- */
    function renderAdminStats() {
        const users = StudentHubDB.getUsers();

        const attVals = users
            .filter(u => u.attendance != null)
            .map(u => Number(u.attendance));

        const cgpaVals = users
            .filter(u => u.cgpa != null)
            .map(u => Number(u.cgpa));

        const weekAgo = Date.now() - 7 * 24 * 60 * 60 * 1000;

        const stats = {
            total: String(users.length),
            active: String(users.filter(u => u.status === "active").length),
            avgAttendance: attVals.length
                ? Math.round(attVals.reduce((a, b) => a + b, 0) / attVals.length) +
                  "%"
                : SH_EMPTY,
            avgCgpa: cgpaVals.length
                ? (cgpaVals.reduce((a, b) => a + b, 0) / cgpaVals.length).toFixed(2)
                : SH_EMPTY,
            newThisWeek: String(
                users.filter(u => {
                    const t = Date.parse(u.createdAt || "");
                    return !isNaN(t) && t >= weekAgo;
                }).length
            )
        };

        document.querySelectorAll("[data-admin-stat]").forEach(el => {
            const key = el.dataset.adminStat;
            if (key in stats) el.textContent = stats[key];
        });
    }

    /* ---- students table ---- */
    function subjectsToText(user) {
        if (!user || !Array.isArray(user.subjects)) return "";
        return user.subjects
            .map(s => s.name + ":" + shClamp(s.attendance))
            .join(", ");
    }

    function parseSubjects(text) {
        return String(text || "")
            .split(",")
            .map(part => {
                const bits = part.split(":");
                const name = (bits[0] || "").trim();
                if (!name) return null;
                return { name: name, attendance: shClamp(bits[1]) };
            })
            .filter(Boolean);
    }

    function renderStudentsTable() {
        const body = document.getElementById("studentTableBody");
        if (!body) return;

        const users = StudentHubDB.getUsers();
        body.innerHTML = "";

        if (!users.length) {
            const row = document.createElement("tr");
            row.innerHTML =
                '<td colspan="8" class="admin-empty-row">' +
                "No students have registered yet." +
                "</td>";
            body.appendChild(row);
            return;
        }

        users.forEach(user => {
            const row = document.createElement("tr");
            const statusLabel = user.status
                ? user.status.charAt(0).toUpperCase() + user.status.slice(1)
                : "Active";

            row.innerHTML =
                "<td>" + shEscape(user.id) + "</td>" +
                '<td><span class="admin-cell-name">' +
                    shEscape(user.name) +
                "</span>" +
                '<span class="admin-cell-sub">' +
                    shEscape(user.email) +
                "</span></td>" +
                "<td>" + shEscape(user.department || SH_EMPTY) + "</td>" +
                "<td>" +
                    (user.semester != null && user.semester !== ""
                        ? "Sem " + shEscape(user.semester)
                        : SH_EMPTY) +
                "</td>" +
                "<td>" +
                    (user.attendance == null
                        ? SH_EMPTY
                        : shClamp(user.attendance) + "%") +
                "</td>" +
                "<td>" +
                    (user.cgpa == null ? SH_EMPTY : Number(user.cgpa).toFixed(2)) +
                "</td>" +
                '<td><span class="admin-status admin-status-' +
                    shEscape(user.status || "active") +
                    '">' +
                    statusLabel +
                "</span></td>" +
                '<td class="admin-row-actions">' +
                    '<button type="button" class="admin-row-btn" ' +
                    'data-action="edit" data-id="' +
                    shEscape(user.id) +
                    '">Edit</button>' +
                    '<button type="button" class="admin-row-btn admin-row-btn-danger" ' +
                    'data-action="delete" data-id="' +
                    shEscape(user.id) +
                    '">Delete</button>' +
                "</td>";

            body.appendChild(row);
        });
    }

    /* ---- edit student ---- */
    function openEditStudent(id) {
        const user = StudentHubDB.findById(id);
        if (!user) return;

        const modal = document.getElementById("editStudentModal");
        const form = document.getElementById("editStudentForm");

        if (!modal || !form) {
            /* fallback: prompt-based editing */
            const att = prompt(
                "Attendance % for " + user.name + " (blank to clear):",
                user.attendance == null ? "" : user.attendance
            );
            if (att === null) return;

            const cgpa = prompt(
                "CGPA (out of 10) for " + user.name + ":",
                user.cgpa == null ? "" : user.cgpa
            );
            if (cgpa === null) return;

            const subs = prompt(
                "Subjects as Name:percent, comma separated:",
                subjectsToText(user)
            );
            if (subs === null) return;

            StudentHubDB.updateUser(user.id, {
                attendance: att.trim() === "" ? null : shClamp(att),
                cgpa: cgpa.trim() === "" ? null : Number(cgpa),
                subjects: parseSubjects(subs)
            });

            renderStudentsTable();
            renderAdminStats();
            adminToast("Saved changes for " + user.name, "success");
            return;
        }

        const setField = (name, value) => {
            const field = form.elements[name];
            if (field) field.value = value;
        };

        const title = modal.querySelector("[data-edit-title]");
        if (title) {
            title.textContent = user.name + " · " + user.id;
        }

        setField("attendance", user.attendance == null ? "" : user.attendance);
        setField("cgpa", user.cgpa == null ? "" : user.cgpa);
        setField("credits", user.credits == null ? "" : user.credits);
        setField("rank", user.rank || "");
        setField("semester", user.semester == null ? "" : user.semester);
        setField("advisor", user.advisor || "");
        setField("status", user.status || "active");
        setField("subjects", subjectsToText(user));

        form.dataset.editingId = user.id;
        modal.hidden = false;
    }

    function bindEditForm() {
        const form = document.getElementById("editStudentForm");
        if (!form || form.dataset.bound === "true") return;
        form.dataset.bound = "true";

        form.addEventListener("submit", event => {
            event.preventDefault();

            const id = form.dataset.editingId;
            if (!id) return;

            const data = new FormData(form);
            const attRaw = String(data.get("attendance") || "").trim();
            const cgpaRaw = String(data.get("cgpa") || "").trim();
            const creditsRaw = String(data.get("credits") || "").trim();
            const semRaw = String(data.get("semester") || "").trim();

            StudentHubDB.updateUser(id, {
                attendance: attRaw === "" ? null : shClamp(attRaw),
                cgpa: cgpaRaw === "" ? null : Number(cgpaRaw),
                credits: creditsRaw === "" ? null : Number(creditsRaw),
                rank: String(data.get("rank") || "").trim(),
                semester: semRaw === "" ? null : semRaw,
                advisor: String(data.get("advisor") || "").trim(),
                status: String(data.get("status") || "active"),
                subjects: parseSubjects(data.get("subjects"))
            });

            const modal = document.getElementById("editStudentModal");
            if (modal) modal.hidden = true;

            renderStudentsTable();
            renderAdminStats();
            adminToast("Student record updated.", "success");
        });
    }

    /* ---- add student ---- */
    function bindAddStudent() {
        const openBtn = document.getElementById("addStudentButton");
        const modal = document.getElementById("studentModal");
        const form = document.getElementById("addStudentForm");

        if (openBtn && modal) {
            openBtn.addEventListener("click", () => {
                if (form) form.reset();
                const msg = document.getElementById("addStudentMessage");
                if (msg) {
                    msg.className = "form-message";
                    msg.innerHTML = "";
                }
                modal.hidden = false;
            });
        }

        if (form && form.dataset.bound !== "true") {
            form.dataset.bound = "true";

            form.addEventListener("submit", event => {
                event.preventDefault();

                const data = new FormData(form);
                const msg = document.getElementById("addStudentMessage");

                const result = StudentHubDB.registerUser({
                    name: data.get("name"),
                    id: data.get("id"),
                    email: data.get("email"),
                    password:
                        String(data.get("password") || "").trim() ||
                        "changeme123",
                    semester: String(data.get("semester") || "").trim() || null
                });

                if (!result.ok) {
                    if (msg) setFormMessage(msg, "error", result.error);
                    return;
                }

                if (modal) modal.hidden = true;
                renderStudentsTable();
                renderAdminStats();
                renderNotifications();
                adminToast("Added " + result.user.name + ".", "success");
            });
        }
    }

    /* ---- table row actions (delegated) ---- */
    function bindTableActions() {
        const body = document.getElementById("studentTableBody");
        if (!body || body.dataset.bound === "true") return;
        body.dataset.bound = "true";

        body.addEventListener("click", event => {
            const btn = event.target.closest("button[data-action]");
            if (!btn) return;

            const id = btn.dataset.id;
            const action = btn.dataset.action;

            if (action === "edit") {
                openEditStudent(id);
            } else if (action === "delete") {
                const user = StudentHubDB.findById(id);
                const label = user ? user.name : id;
                if (confirm("Delete " + label + "? This cannot be undone.")) {
                    StudentHubDB.removeUser(id);
                    renderStudentsTable();
                    renderAdminStats();
                    adminToast("Deleted " + label + ".", "success");
                }
            }
        });
    }

    /* ---- global search ---- */
    function bindSearch() {
        const search = document.getElementById("adminGlobalSearch");
        if (!search) return;

        search.addEventListener("input", () => {
            const term = search.value.trim().toLowerCase();
            const rows = document.querySelectorAll("#studentTableBody tr");

            rows.forEach(row => {
                if (row.querySelector(".admin-empty-row")) return;
                const match = row.textContent.toLowerCase().indexOf(term) > -1;
                row.hidden = !match;
            });
        });
    }

    /* ---- notifications ---- */
    function renderNotifications() {
        const badge = document.getElementById("adminNotificationBadge");
        const count = StudentHubDB.unreadCount();

        if (badge) {
            badge.textContent = String(count);
            badge.hidden = count === 0;
        }

        const list = document.getElementById("adminNotificationList");
        if (!list) return;

        const notes = StudentHubDB.getNotifications();
        list.innerHTML = "";

        if (!notes.length) {
            const empty = document.createElement("li");
            empty.className = "admin-note-empty";
            empty.textContent = "You're all caught up.";
            list.appendChild(empty);
            return;
        }

        notes.forEach(note => {
            const item = document.createElement("li");
            item.className =
                "admin-note" + (note.read ? "" : " admin-note-unread");

            let when = "";
            const t = Date.parse(note.time || "");
            if (!isNaN(t)) {
                when = new Date(t).toLocaleString();
            }

            item.innerHTML =
                '<span class="admin-note-title">' +
                    shEscape(note.title) +
                "</span>" +
                '<span class="admin-note-text">' +
                    shEscape(note.text) +
                "</span>" +
                '<span class="admin-note-time">' + shEscape(when) + "</span>";

            list.appendChild(item);
        });
    }

    function bindNotifications() {
        const btn = document.getElementById("adminNotificationButton");
        const panel = document.getElementById("adminNotificationPanel");

        if (btn && panel) {
            btn.addEventListener("click", event => {
                event.stopPropagation();
                const opening = panel.hidden;
                panel.hidden = !opening;

                if (opening) {
                    StudentHubDB.markNotificationsRead();
                    renderNotifications();
                }
            });

            document.addEventListener("click", event => {
                if (panel.hidden) return;
                if (
                    event.target.closest("#adminNotificationPanel") ||
                    event.target.closest("#adminNotificationButton")
                ) {
                    return;
                }
                panel.hidden = true;
            });
        }

        const clearBtn = document.getElementById("adminNotificationClear");
        if (clearBtn) {
            clearBtn.addEventListener("click", () => {
                StudentHubDB.clearNotifications();
                renderNotifications();
            });
        }
    }

    /* ---- theme toggle ---- */
    function updateAdminThemeIcon() {
        const btn = document.getElementById("adminThemeToggle");
        if (!btn) return;
        const icon = btn.querySelector("i");
        if (!icon) return;

        const isBlue = document.body.classList.contains("theme-blue");
        icon.className = isBlue ? "fa-solid fa-sun" : "fa-solid fa-moon";
    }

    function bindThemeToggle() {
        const btn = document.getElementById("adminThemeToggle");
        if (!btn) return;

        updateAdminThemeIcon();

        btn.addEventListener("click", () => {
            const next = document.body.classList.contains("theme-blue")
                ? "teal"
                : "blue";
            if (typeof changeTheme === "function") {
                changeTheme(next);
            }
            updateAdminThemeIcon();
        });
    }

    /* ---- sidebar / section navigation ---- */
    function bindNavigation() {
        const items = document.querySelectorAll(".admin-nav-item[data-section]");
        const sections = document.querySelectorAll(".admin-section");
        const sidebar = document.getElementById("adminSidebar");
        const overlay = document.getElementById("adminMobileOverlay");

        const closeSidebar = () => {
            if (sidebar) sidebar.classList.remove("is-open");
            if (overlay) overlay.classList.remove("is-open");
        };

        items.forEach(item => {
            item.addEventListener("click", () => {
                const target = item.dataset.section;

                items.forEach(i => i.classList.remove("is-active"));
                item.classList.add("is-active");

                sections.forEach(section => {
                    section.hidden = section.id !== "section-" + target;
                });

                const heading = document.getElementById("adminSectionTitle");
                if (heading) {
                    heading.textContent = item.dataset.label || item.textContent.trim();
                }

                closeSidebar();
            });
        });

        const menuBtn = document.getElementById("adminMenuButton");
        if (menuBtn && sidebar) {
            menuBtn.addEventListener("click", () => {
                sidebar.classList.toggle("is-open");
                if (overlay) overlay.classList.toggle("is-open");
            });
        }

        if (overlay) {
            overlay.addEventListener("click", closeSidebar);
        }
    }

    /* ---- modals (generic close) ---- */
    function bindModals() {
        document.querySelectorAll(".admin-modal").forEach(modal => {
            modal.addEventListener("click", event => {
                if (event.target === modal) modal.hidden = true;
            });
        });

        document.querySelectorAll("[data-modal-close]").forEach(btn => {
            btn.addEventListener("click", () => {
                const modal = btn.closest(".admin-modal");
                if (modal) modal.hidden = true;
            });
        });
    }

    /* ---- login gate ---- */
    function showPortal() {
        if (loginScreen) loginScreen.hidden = true;
        if (portal) portal.hidden = false;
        renderStudentsTable();
        renderAdminStats();
        renderNotifications();
    }

    function showLogin() {
        if (portal) portal.hidden = true;
        if (loginScreen) loginScreen.hidden = false;
    }

    const loginForm = document.getElementById("adminLoginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", event => {
            event.preventDefault();

            const uField = document.getElementById("adminUsername");
            const pField = document.getElementById("adminPassword");
            const errBox = document.getElementById("adminLoginError");

            const u = uField ? uField.value.trim() : "";
            const p = pField ? pField.value : "";

            if (u === ADMIN_USER && p === ADMIN_PASS) {
                StudentHubDB.setAdminLoggedIn(true);
                if (errBox) errBox.hidden = true;
                showPortal();
            } else if (errBox) {
                errBox.hidden = false;
                errBox.textContent =
                    "Invalid credentials. Please try again.";
            }
        });
    }

    const pwToggle = document.getElementById("adminPasswordToggle");
    if (pwToggle) {
        pwToggle.addEventListener("click", () => {
            const field = document.getElementById("adminPassword");
            if (!field) return;
            const show = field.type === "password";
            field.type = show ? "text" : "password";
            const icon = pwToggle.querySelector("i");
            if (icon) {
                icon.className = show
                    ? "fa-solid fa-eye-slash"
                    : "fa-solid fa-eye";
            }
        });
    }

    const logoutBtn = document.getElementById("adminLogout");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
            StudentHubDB.setAdminLoggedIn(false);
            showLogin();
        });
    }

    /* ---- current year ---- */
    document.querySelectorAll("#adminPortal [data-current-year]").forEach(el => {
        el.textContent = new Date().getFullYear();
    });

    /* ---- wire everything ---- */
    bindNavigation();
    bindThemeToggle();
    bindModals();
    bindAddStudent();
    bindEditForm();
    bindTableActions();
    bindSearch();
    bindNotifications();

    if (StudentHubDB.isAdminLoggedIn()) {
        showPortal();
    } else {
        showLogin();
    }
}


/* =========================================================
   ADMIN PORTAL — supplemental wiring
   (local table search, section-action shortcuts, brand
   link, and honest guards for preview-only demo forms).
   Appended block; reuses initializeAdminPortal()'s
   sidebar-nav click handler via goToSection().
========================================================= */
(function () {

    function initAdminExtras() {

        const portal = document.getElementById("adminPortal");
        if (!portal) return;

        /* toast helper — mirrors adminToast()'s CSS contract */
        function extrasToast(text, type) {
            const toast = document.getElementById("adminToast");
            if (!toast) return;
            toast.textContent = text;
            toast.className =
                "admin-toast-box show " + (type ? "admin-toast-" + type : "");
            clearTimeout(toast._timer);
            toast._timer = setTimeout(function () {
                toast.className = "admin-toast-box";
            }, 3200);
        }

        /* switch sections by re-using the real sidebar nav handler */
        function goToSection(target) {
            if (!target) return;
            const nav = document.querySelector(
                '.admin-nav-item[data-section="' + target + '"]'
            );
            if (nav) nav.click();
        }

        /* dashboard quick-action buttons */
        document.querySelectorAll("[data-section-action]").forEach(function (btn) {
            btn.addEventListener("click", function (event) {
                event.preventDefault();
                goToSection(btn.dataset.sectionAction);
            });
        });

        /* brand logo returns to the dashboard */
        document.querySelectorAll(".admin-brand[data-section]").forEach(function (brand) {
            brand.addEventListener("click", function (event) {
                event.preventDefault();
                goToSection(brand.dataset.section);
            });
        });

        /* local students-table search (topbar search wired separately) */
        const localSearch = document.getElementById("studentSearch");
        if (localSearch) {
            localSearch.addEventListener("input", function () {
                const term = localSearch.value.trim().toLowerCase();
                document.querySelectorAll("#studentTableBody tr").forEach(function (row) {
                    if (row.querySelector(".admin-empty-row")) return;
                    row.hidden = row.textContent.toLowerCase().indexOf(term) === -1;
                });
            });
        }

        /* preview-only forms: stop the page reload, acknowledge honestly */
        const demoForms = [
            {
                id: "createEventForm",
                msg: "Events is a preview section in this build — nothing was saved."
            },
            {
                id: "announcementForm",
                msg: "Announcements is a preview section in this build — nothing was saved."
            }
        ];

        demoForms.forEach(function (cfg) {
            const form = document.getElementById(cfg.id);
            if (!form) return;
            form.addEventListener("submit", function (event) {
                event.preventDefault();
                extrasToast(cfg.msg);
                form.reset();
                const modal = form.closest(".admin-modal");
                if (modal) modal.hidden = true;
            });
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initAdminExtras);
    } else {
        initAdminExtras();
    }

})();
