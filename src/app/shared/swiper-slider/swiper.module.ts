import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { SwiperComponent } from './swiper.component';
import { SwiperDirective } from './swiper.directive';

@NgModule({
  declarations: [
    SwiperComponent,
    SwiperDirective
  ],
  imports: [
    CommonModule
  ],
  exports: [
    SwiperComponent,
    SwiperDirective
  ],
  schemas: [CUSTOM_ELEMENTS_SCHEMA],
})
export class NgxSwiperModule { }
