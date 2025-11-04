import { Component, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { Subscription } from 'rxjs';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { SwiperOptions } from 'swiper/types';

import { 
  CarouselService
} from '../../../core';

import { VideoComponent } from '../../../shared/components';

@Component({
  selector: 'app-carousel',
  templateUrl: './carousel.component.html',
  encapsulation: ViewEncapsulation.None
})
export class CarouselComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription[] = [];
  
  constructor(
    private modal: NgbModal,
    // Services
    public carousel: CarouselService,
  ) {}   

  ngOnInit(): void {
    const sb = this.carousel.getCarousel().pipe(
    ).subscribe();
    this.subscriptions.push(sb);   
  } 
  
  openVideo(videoId: string, videoName?: string) {
    const modalRef = this.modal.open(VideoComponent, { size: 'xl', centered: true, windowClass: 'modal-custom' });
    modalRef.componentInstance.videoId = videoId;
    modalRef.componentInstance.videoName = videoName;
  }  
    
  ngOnDestroy(): void {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
  } 

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
