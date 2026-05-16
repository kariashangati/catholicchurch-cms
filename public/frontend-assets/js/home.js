document.addEventListener('DOMContentLoaded', function () {
  const createSwiper = (selector, options) => {
    const el = document.querySelector(selector);
    if (!el) return null;
    return new Swiper(selector, options);
  };

  const heroSlides = document.querySelectorAll('.heroSwiper .swiper-wrapper > .swiper-slide').length;
  const announcementSlides = document.querySelectorAll('.announcementSwiper .swiper-wrapper > .swiper-slide').length;
  const projectSlides = document.querySelectorAll('.projectSwiper .swiper-wrapper > .swiper-slide').length;
  const zoneSlides = document.querySelectorAll('.zoneSwiper .swiper-wrapper > .swiper-slide').length;
  const gallerySlides = document.querySelectorAll('.gallerySwiper .swiper-wrapper > .swiper-slide').length;

  createSwiper('.heroSwiper', {
    loop: heroSlides > 1,
    speed: 950,
    effect: 'fade',
    fadeEffect: { crossFade: true },
    autoplay: heroSlides > 1 ? {
      delay: 6500,
      disableOnInteraction: false
    } : false,
    navigation: {
      nextEl: '.hero-slider .swiper-button-next',
      prevEl: '.hero-slider .swiper-button-prev'
    },
    pagination: {
      el: '.hero-slider .swiper-pagination',
      clickable: true
    }
  });

  createSwiper('.announcementSwiper', {
    slidesPerView: 1,
    spaceBetween: 14,
    loop: announcementSlides > 3,
    speed: 800,
    watchOverflow: true,
    observer: true,
    observeParents: true,
    autoplay: announcementSlides > 1 ? {
      delay: 3600,
      disableOnInteraction: false,
      pauseOnMouseEnter: true
    } : false,
    pagination: {
      el: '.announcementSwiper .swiper-pagination',
      clickable: true
    },
    navigation: {
      nextEl: '.announcementSwiper .swiper-button-next',
      prevEl: '.announcementSwiper .swiper-button-prev'
    },
    breakpoints: {
      640: {
        slidesPerView: 1.15,
        spaceBetween: 14
      },
      768: {
        slidesPerView: 2,
        spaceBetween: 16
      },
      1200: {
        slidesPerView: 3,
        spaceBetween: 18
      }
    }
  });

  createSwiper('.projectSwiper', {
    slidesPerView: 1,
    spaceBetween: 14,
    loop: projectSlides > 3,
    speed: 850,
    watchOverflow: true,
    observer: true,
    observeParents: true,
    autoplay: projectSlides > 1 ? {
      delay: 3700,
      disableOnInteraction: false,
      pauseOnMouseEnter: true
    } : false,
    pagination: {
      el: '.projectSwiper .swiper-pagination',
      clickable: true
    },
    navigation: {
      nextEl: '.projectSwiper .swiper-button-next',
      prevEl: '.projectSwiper .swiper-button-prev'
    },
    breakpoints: {
      640: {
        slidesPerView: 1.15,
        spaceBetween: 14
      },
      768: {
        slidesPerView: 2,
        spaceBetween: 16
      },
      1200: {
        slidesPerView: 3,
        spaceBetween: 18
      }
    }
  });

  createSwiper('.zoneSwiper', {
    slidesPerView: 1,
    spaceBetween: 14,
    loop: zoneSlides > 3,
    speed: 850,
    watchOverflow: true,
    observer: true,
    observeParents: true,
    autoplay: zoneSlides > 1 ? {
      delay: 3900,
      disableOnInteraction: false,
      pauseOnMouseEnter: true
    } : false,
    pagination: {
      el: '.zoneSwiper .swiper-pagination',
      clickable: true
    },
    navigation: {
      nextEl: '.zoneSwiper .swiper-button-next',
      prevEl: '.zoneSwiper .swiper-button-prev'
    },
    breakpoints: {
      640: {
        slidesPerView: 1.15,
        spaceBetween: 14
      },
      768: {
        slidesPerView: 2,
        spaceBetween: 16
      },
      1200: {
        slidesPerView: 3,
        spaceBetween: 18
      }
    }
  });

  createSwiper('.gallerySwiper', {
    slidesPerView: 1,
    spaceBetween: 14,
    loop: gallerySlides > 3,
    speed: 850,
    watchOverflow: true,
    observer: true,
    observeParents: true,
    autoplay: gallerySlides > 1 ? {
      delay: 3400,
      disableOnInteraction: false,
      pauseOnMouseEnter: true
    } : false,
    pagination: {
      el: '.gallerySwiper .swiper-pagination',
      clickable: true
    },
    navigation: {
      nextEl: '.gallerySwiper .swiper-button-next',
      prevEl: '.gallerySwiper .swiper-button-prev'
    },
    breakpoints: {
      640: {
        slidesPerView: 1.15,
        spaceBetween: 14
      },
      768: {
        slidesPerView: 2,
        spaceBetween: 16
      },
      1200: {
        slidesPerView: 3,
        spaceBetween: 18
      }
    }
  });
});