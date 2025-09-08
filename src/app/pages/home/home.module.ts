import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

// 3rd-Party plugins variables
import { InlineSVGModule } from 'ng-inline-svg-2';

// Components
import { SliderComponent } from './slider/slider.component';
import { TechnologyComponent } from './technology/technology.component';
import { CarouselComponent } from './carousel/carousel.component';

import { HomeRoutingModule } from './home-routing.module';
import { HomeComponent } from './home.component';

import { CoreModule } from '../../core';

import { 
  NgxSwiperModule,
  NgxOptimizedImageModule
 } from '../../shared';

@NgModule({
  declarations: [
    HomeComponent,
    SliderComponent,
    TechnologyComponent,
    CarouselComponent,
  ],
  imports: [
    CommonModule,
    HomeRoutingModule,
    // Plugins
    NgxSwiperModule,
    NgxOptimizedImageModule,  
    InlineSVGModule, 
    CoreModule
  ],
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})
export class HomeModule { }
