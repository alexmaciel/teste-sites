import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TitleStrategy } from '@angular/router';

import { SeoService, TranslateTitleStrategy } from './utils';

@NgModule({
  imports: [
    CommonModule,
  ],
  providers: [
    SeoService,
    { provide: TitleStrategy, useClass: TranslateTitleStrategy },     
  ],
})
export class CoreModule { }
