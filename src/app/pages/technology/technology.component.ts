import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';

// typical import
import { SwiperOptions } from 'swiper/types';

import { 
  TechnologyService,
  TechnologyPicturesService
} from '../../core';

@Component({
  selector: 'app-technology',
  templateUrl: './technology.component.html'
})
export class TechnologyComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription[] = [];

  constructor(
    // Services
    public technology: TechnologyService,
    public pictures: TechnologyPicturesService,
  ) {}   

  ngOnInit(): void {
    this.loadTechnology();
    this.loadPictures();
  }  
    
  loadTechnology() {
    const sb = this.technology.getTechnology().pipe(
    ).subscribe();
    this.subscriptions.push(sb);   
  }  

  loadPictures() {
    const sb = this.pictures.getTechnologyPictures().pipe(
    ).subscribe();
    this.subscriptions.push(sb);       
  }  
  
  ngOnDestroy(): void {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
  }  

  async animateActiveSlide(swiper: any, index: number): Promise<void> {
    // typical import
    const { gsap } = await import('gsap');

    if (typeof document === 'undefined' || !swiper) return;

    const img = swiper.slidesEl.querySelectorAll('.slider-image');
    const tl = gsap.timeline();
    if(index >= 0) {
      if(img && img[index]) {
        tl.fromTo(img[index],
          { autoAlpha: 0, x: -65, },
          { autoAlpha: 1, x: 0, duration: 0.8, ease: 'power3.out' }
        );
      }       
    }    
  }

  // Swiper
  config: SwiperOptions = {
    slidesPerView: 1,
    spaceBetween: 0,
    keyboard: true,
    grabCursor: true,
    navigation: false,
    pagination: {
      enabled: true,
      type: 'bullets',
      el: '.technology-pagination'
    },  
    on: {
      init: (swiper) => {
        this.animateActiveSlide(swiper, 0);
      },      
      slideChange: (swiper) => this.animateActiveSlide(swiper, swiper.activeIndex)
    } 
  }    
}
