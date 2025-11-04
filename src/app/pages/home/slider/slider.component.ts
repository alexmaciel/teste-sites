import { Component, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { Subscription } from 'rxjs';

// typical import
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
  ) {}

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
    
  async animateActiveSlide(swiper: any, index: number): Promise<void> {
    // typical import
    const { gsap } = await import('gsap');

    if (typeof document === 'undefined' || !swiper) return;

    const img = swiper.slidesEl.querySelectorAll('.slider-image');
    const content = swiper.slidesEl.querySelectorAll('.slider-content');
    const tl = gsap.timeline();
    if(index >= 0) {
      if(img && img[index]) {
        tl.fromTo(img[index],
          { autoAlpha: 0, x: 50, scale: 0.8 },
          { autoAlpha: 1, x: 0, scale: 1, duration: 0.8, ease: 'power3.out' }
        );
      }
      if (content && content[index]) {
        const title = content[index].querySelector('.slider-title');  
        tl.fromTo(title,
          { autoAlpha: 0, x: 50 },
          { autoAlpha: 1, x: 0, duration: 0.6, ease: 'power2.out' }
        );
      }          
    }    
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
    on: {
      init: (swiper) => {
        this.animateActiveSlide(swiper, 0);
      },      
      slideChange: (swiper) => this.animateActiveSlide(swiper, swiper.activeIndex)
    }    
  }   
  
}
