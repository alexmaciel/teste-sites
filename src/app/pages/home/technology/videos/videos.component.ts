import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';

import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { SwiperOptions } from 'swiper/types';

import { TechnologyVideoService } from '../../../../core';

@Component({
  selector: 'app-videos',
  templateUrl: './videos.component.html',
  host: {'app-technology-index-video': 'index-technology-video'},
})
export class VideosComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription[] = [];

  players: YT.Player[] = [];

  constructor(
    public modal: NgbActiveModal,
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

  onPlayerReady(e: YT.PlayerEvent, index: number) {
    this.players[index] = e.target;
  }

  onPlayerState(e: YT.OnStateChangeEvent, index: number) {
    console.log(`Player ${index} mudou de estado`, e.data);
  }

  onPlayerError(e: YT.OnErrorEvent, index: number) {
    console.error(`Erro no player ${index}`, e.data);
  }
     

  // Swiper
  config: SwiperOptions = {
    slidesPerView: 1,
    spaceBetween: 0,
    grabCursor: true,
    effect: "cube",
    cubeEffect: {
      shadow: true,
      slideShadows: true,
      shadowOffset: 20,
      shadowScale: 0.94,
    },
    navigation: {
      enabled: true,
      nextEl: '.swiper-button-slider-next',
      prevEl: '.swiper-button-slider-prev',
    },     
    pagination: {
      type: 'bullets',
      el: '.videos-technology-pagination'
    },  
    on: {
      slideChange: () => {
        this.players.forEach((p) => {
          if (p?.pauseVideo) p.pauseVideo();
        });
      }
    },    
  }   
}
