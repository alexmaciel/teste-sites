import { Component, ViewEncapsulation } from '@angular/core';

import { SwiperOptions } from 'swiper/types';

@Component({
  selector: 'app-carousel',
  templateUrl: './carousel.component.html',
  encapsulation: ViewEncapsulation.None
})
export class CarouselComponent {


  // Swiper
  config: SwiperOptions = {
    slidesPerView: 1,
    spaceBetween: 0,
    grabCursor: true,
    navigation: false,
    pagination: {
      enabled: true,
      type: 'bullets',
      el: '.carousel-pagination'
    },  
  }   
}
