import { Component, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { Subscription } from 'rxjs';

import { SwiperOptions } from 'swiper/types';

import { 
  SlideService, 
} from '../../../core';

@Component({
  selector: 'app-slider',
  templateUrl: './slider.component.html',
  encapsulation: ViewEncapsulation.None,
})
export class SliderComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription[] = [];

  constructor(
    // Services
    public slides: SlideService
  ){ }

  ngOnInit(): void {
    this.loadSlides();
  }

  loadSlides() {
    const sb = this.slides.getSlides().subscribe();
    this.subscriptions.push(sb);      
  }  

  ngOnDestroy() {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
  }   
    
   // Swiper
   config: SwiperOptions = {
    slidesPerView: 1,
    spaceBetween: 0,
    speed: 900,
    grabCursor: false,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
    },
    effect: 'fade',
    navigation: false,
    pagination: {
      enabled: true,
      type: 'bullets',
      el: '.slider-pagination'
    },  
  }   
  
}
