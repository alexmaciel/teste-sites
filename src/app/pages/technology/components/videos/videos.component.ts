import { Component, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { Subscription } from 'rxjs';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { SwiperOptions } from 'swiper/types';

import { VideoComponent } from '../../../../shared/components';

import { 
  TechnologyVideoService
} from '../../../../core';

@Component({
  selector: 'app-videos',
  templateUrl: './videos.component.html',
  encapsulation: ViewEncapsulation.None,
  host: {'app-technology-filter-video': 'technology-video'},
})
export class VideosComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription[] = [];

  constructor(
    private modal: NgbModal,
    // Services
    public videos: TechnologyVideoService,
  ) {}   

  ngOnInit(): void {
    const sb = this.videos.getTechnologyVideos().pipe(
    ).subscribe();
    this.subscriptions.push(sb);   
  } 
  
  ngOnDestroy(): void {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
  } 
     
  openVideo(videoId: string, videoName?: string) {
    const modalRef = this.modal.open(VideoComponent, { size: 'xl', centered: true, windowClass: 'modal-custom' });
    modalRef.componentInstance.videoId = videoId;
    modalRef.componentInstance.videoName = videoName;
  }  

  config: SwiperOptions = {
    slidesPerView: "auto",
    freeMode: false,
    centeredSlides: false,
    spaceBetween: 0,
    grabCursor: true,
    navigation: {
      enabled: false,
    },
    pagination: {
      enabled: true,
      type: 'bullets',
      el: '.videos-techonology-pagination',
    },         
    breakpoints: {
      '320': {
        spaceBetween: 0,
        centeredSlides: false,
      },
      '768': {
        spaceBetween: 0,
        centeredSlides: false,
      },
      '1024': {
        spaceBetween: 3,
      },
      '1440': {
        spaceBetween: 4,
      }      
    }
  };
}
