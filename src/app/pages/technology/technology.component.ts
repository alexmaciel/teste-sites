import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';

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
  }    
}
