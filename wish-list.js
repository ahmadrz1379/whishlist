document.addEventListener("DOMContentLoaded", () => {
    const button = document.getElementById("wishlist-action");
    if (button) {
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
              button.textContent =
                newStatus === 1 ? "Remove from wishlist" : "Add to wishlist";
            } else {
              alert(data.message || "An error occurred. Please try again.");
            }
          })
          .catch((error) => {
            console.error("Fetch error:", error);
            alert("An unexpected error occurred. Please try again later.");
          });
      });
    }
  });
  
  document.addEventListener("DOMContentLoaded", () => {
    const ajaxurl = custom_ajax.ajax_url;
  
    const remove_buttons = document.querySelectorAll(".remove-from-wishlist");
    console.log("DOM loaded", "remove_buttons", remove_buttons);
  
    if (0 < remove_buttons.length) {
      console.log("should work");
  
      remove_buttons.forEach((button) => {
        console.log(button);
  
        button.addEventListener("click", () => {
          console.log("clicked!");
  
          const nonce = button.getAttribute("data-nonce");
          const product_id = button.getAttribute("data-id");
          const slideElement = button.closest(".swiper-slide");
  
          fetch(ajaxurl, {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
              action: "update_wishlist",
              post_id: product_id,
              status: 0, // Toggle status
              nonce: nonce,
            }),
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                alert("محصول با موفقیت حذف شد");
  
                // Remove the slide from Swiper
                const swiper = document.querySelector(".wishlist-swiper").swiper;
  
                if (swiper && slideElement) {
                  const slideIndex = Array.from(
                    slideElement.parentNode.children
                  ).indexOf(slideElement);
                  swiper.removeSlide(slideIndex); // Remove the slide from Swiper
  
                  // Check if slides are less than 3
                  if (swiper.slides.length < 3) {
                    // Destroy Swiper and reset layout
                    swiper.destroy(true, true);
                    resetStaticLayout();
                  }
  
                  // Optionally, check if there are no slides left
                  if (swiper.slides.length === 0) {
                    document.querySelector(
                      ".container_swiper_wishlist"
                    ).innerHTML = "<p>Your wishlist is empty.</p>";
                  }
                }
              }  
            })
            .catch((error) => {
              console.error("Fetch error:", error); 
            });
        });
      });
    }
  
    // Function to reset layout when less than 3 slides
    function resetStaticLayout() {
      const swiperWrapper = document.querySelector(".swiper-wrapper");
      const swiperContainer = document.querySelector(".wishlist-swiper");
  
      // Keep existing slides visible in a static layout
      swiperWrapper.style.display = "flex";
      swiperWrapper.style.gap = "10px";
      swiperWrapper.style.justifyContent = "center";
  
      // Remove navigation arrows
      document.querySelector(".whishlist_next").style.display = "none";
      document.querySelector(".whishlist_perv").style.display = "none";
  
      // Remove swiper-specific classes
      swiperContainer.classList.remove("swiper");
      swiperWrapper.classList.remove("swiper-wrapper");
      Array.from(swiperWrapper.children).forEach((slide) => {
        slide.classList.remove("swiper-slide");
      });
    }
  });
  