/* =====================================================
   TESTIMONIALS PAGE
===================================================== */

document.addEventListener("DOMContentLoaded", function () {

    const filterButtons =
        document.querySelectorAll(".review-filter");

    const reviewCards =
        document.querySelectorAll(".review-card");


    filterButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            /* Remove active state */
            filterButtons.forEach(function (btn) {
                btn.classList.remove("active");
            });

            /* Add active state */
            button.classList.add("active");

            const selectedFilter =
                button.getAttribute("data-filter");


            reviewCards.forEach(function (card) {

                const cardCategory =
                    card.getAttribute("data-category");


                if (
                    selectedFilter === "all" ||
                    selectedFilter === cardCategory
                ) {

                    card.style.display = "block";

                    setTimeout(function () {
                        card.style.opacity = "1";
                        card.style.transform = "translateY(0)";
                    }, 10);

                } else {

                    card.style.opacity = "0";
                    card.style.transform = "translateY(10px)";

                    setTimeout(function () {
                        card.style.display = "none";
                    }, 200);

                }

            });

        });

    });

});