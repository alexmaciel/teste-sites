import { AfterViewInit, Component, Input, ViewEncapsulation } from '@angular/core';

import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

import { JsonLdService } from '../../../core/utils';

@Component({
  selector: 'app-video',
  templateUrl: './video.component.html',
  encapsulation: ViewEncapsulation.None,
})
export class VideoComponent implements AfterViewInit {
  @Input() videoId: string | undefined;
  @Input() videoName: string | undefined | any;
  
  player!: YT.Player;
  
  constructor(
    public modal: NgbActiveModal,
    // seo
    private readonly jsonLdService: JsonLdService,     
  ) { }

  ngAfterViewInit(): void {
    const jsonLd = this.jsonLdService.getObject('VideoObject', {
      name: this.videoName,
      thumbnailUrl: `https://img.youtube.com/vi/${this.videoId}/hqdefault.jpg`,
      embedUrl: `https://www.youtube.com/embed/${this.videoId}`,
      publisher: {
        "@type": "Organization",
        "name": "metaBix"
      }      
    });        
    this.jsonLdService.setData(jsonLd);       
  }

  
  onPlayerReady(e: YT.PlayerEvent) {
    console.log('Player pronto', e);
  }

  onPlayerState(e: YT.OnStateChangeEvent) {
    console.log('Mudança de estado', e.data);
  }

  onPlayerError(e: YT.OnErrorEvent) {
    console.error('Erro do player', e.data);
  }
}
