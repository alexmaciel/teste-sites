import { AfterViewInit, Component, ElementRef, Inject, PLATFORM_ID, ViewChild } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

// typical import
import { SwiperOptions } from 'swiper/types';

@Component({
  selector: 'app-protocol',
  templateUrl: './protocol.component.html'
})
export class ProtocolComponent implements AfterViewInit {
  @ViewChild('contentContainer', { static: true }) contentContainer!: ElementRef; 

  constructor(@Inject(PLATFORM_ID) private platformId: object) {}

  ngAfterViewInit(): void {
    if (isPlatformBrowser(this.platformId)) {;
    }
  }

  async animateActiveSlide(swiper: any, index: number): Promise<void> {
    // typical import
    const { gsap } = await import('gsap');

    if (typeof document === 'undefined' || !swiper) return;

    const img = swiper.slidesEl.querySelectorAll('.protocol-image');
    const content = swiper.slidesEl.querySelectorAll('.protocol-content');
    const tl = gsap.timeline();
    if(index >= 0) {
      if(img && img[index]) {
        tl.fromTo(img && img[index],
          { autoAlpha: 0, x: 50, scale: 0.8 },
          { autoAlpha: 1, x: 0, scale: 1, duration: 0.8, ease: 'power3.out' }
        );
      }
      if (content && content[index]) {
        const text = content[index].querySelector('.content-marker');  
        tl.fromTo(text,
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
    grabCursor: true,
    navigation: false,
    keyboard: { enabled: true},
    pagination: {
      enabled: true,
      type: 'bullets',
      el: '.protocol-pagination'
    },
    on: {
      init: (swiper) => {
        this.animateActiveSlide(swiper, 0);
      },      
      slideChange: (swiper) => this.animateActiveSlide(swiper, swiper.activeIndex)
    }
  }     
}
