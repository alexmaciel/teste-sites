import { Component, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { Subscription } from 'rxjs';

import { SwiperOptions } from 'swiper/types';
import { PartneService } from '../../services';

@Component({
  selector: 'app-cases',
  templateUrl: './cases.component.html',
  encapsulation: ViewEncapsulation.None
})
export class CasesComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription[] = [];
  
  constructor(
    // Services
    public partners: PartneService,
  ){}

  ngOnInit(): void {
    this.loadPartners();
  }

  loadPartners() {
    const sb = this.partners.getPartners().pipe(
    ).subscribe();
    this.subscriptions.push(sb);       
  }  

  ngOnDestroy() {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
  }
    
  // Swiper
  config: SwiperOptions = {
    slidesPerView: 6,
    spaceBetween: 0,
    loop: false,
    grabCursor: true,
    keyboard: false,
    touchStartPreventDefault: true,
    autoplay: {
      delay: 8000,
      disableOnInteraction: false,
    },    
    pagination: {
      type: 'bullets', 
      el: '.partner-pagination',
    },
    navigation: false,   
    breakpoints: {
      '320': {
        slidesPerView: 2,
      },
      '768': {
        slidesPerView: 2,
      },
      '1024': {
        slidesPerView: 4,
      },
      '1199': {
        slidesPerView: 5,
      },
      '1440': {
        slidesPerView: 6,
      }            
    }     
  };  
}
