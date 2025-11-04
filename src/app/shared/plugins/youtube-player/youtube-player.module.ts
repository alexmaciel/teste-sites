import {NgModule} from '@angular/core';
import {NgxYouTubePlayer} from './youtube-player';

// 3rd-Party plugins variables
import { YOUTUBE_PLAYER_CONFIG, YouTubePlayer, YouTubePlayerModule} from '@angular/youtube-player';

@NgModule({
  imports: [YouTubePlayer, YouTubePlayerModule],
  declarations: [NgxYouTubePlayer],
  exports: [NgxYouTubePlayer],
  providers: [{
    provide: YOUTUBE_PLAYER_CONFIG,
    useValue: {
      loadApi: true,
    }
  }]  
})
export class NgxYouTubePlayerModule {}