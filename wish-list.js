document.addEventListener("DOMContentLoaded", () => {
    const button = document.getElementById("wishlist-action");
    if (!button) {
        console.error("Wishlist button not found");
        return;
    }

    const ajaxurl = custom_ajax.ajax_url;
    const nonce = button.dataset.nonce;

    button.addEventListener("click", () => {
        const postId = button.dataset.id;
        const currentStatus = parseInt(button.dataset.status);

        fetch(ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                action: "update_wishlist",
                post_id: postId,
                status: currentStatus === 1 ? 0 : 1, // Toggle status
                nonce: nonce,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const newStatus = currentStatus === 1 ? 0 : 1;
                    button.dataset.status = newStatus;
                    button.textContent = newStatus === 1 ? "Remove from wishlist" : "Add to wishlist";
                } else {
                    alert(data.message || "An error occurred. Please try again.");
                }
            })
            .catch((error) => {
                console.error("Fetch error:", error);
                alert("An unexpected error occurred. Please try again later.");
            });
    });
});
