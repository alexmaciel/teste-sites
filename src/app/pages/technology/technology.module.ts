import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

// 3rd-Party plugins variables
import { InlineSVGModule } from 'ng-inline-svg-2';

// Components
import { ItemsComponent } from './components/items/items.component';
import { VideosComponent } from './components/videos/videos.component';
import { ProtocolComponent } from './components/protocol/protocol.component';

import { TechnologyRoutingModule } from './technology-routing.module';
import { TechnologyComponent } from './technology.component';


import { 
  SharedModule,
  // Plugins
  NgxSwiperModule,
  NgxYouTubePlayerModule,
  NgxOptimizedImageModule,
 } from '../../shared';

@NgModule({
  declarations: [
    TechnologyComponent,
    ItemsComponent,
    VideosComponent,
    ProtocolComponent
  ],
  imports: [
    CommonModule,
    TechnologyRoutingModule,
    // Plugins
    NgxSwiperModule,
    NgxOptimizedImageModule,   
    NgxYouTubePlayerModule,
    InlineSVGModule,
    SharedModule    
  ],
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})
export class TechnologyModule { }
