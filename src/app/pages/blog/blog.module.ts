import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

// 3rd-Party plugins variables
import { InlineSVGModule } from 'ng-inline-svg-2';
import { NgbTooltipModule } from '@ng-bootstrap/ng-bootstrap';

import { BlogContentComponent } from './blog-content/blog-content.component';
import { BlogFilterComponent } from './blog-content/blog-filter/blog-filter.component';
import { BlogSortComponent } from './blog-content/blog-sort/blog-sort.component';
import { BlogListComponent } from './blog-content/blog-list/blog-list.component';
// Details
import { BlogDetailsComponent } from './blog-details/blog-details.component';

import { BlogRoutingModule } from './blog-routing.module';
import { BlogComponent } from './blog.component';

import { 
  NgxSwiperModule,
  NgxOptimizedImageModule
 } from '../../shared';

import { CoreModule } from '../../core';

@NgModule({
  declarations: [
    BlogComponent,
    BlogContentComponent,
    BlogFilterComponent,
    BlogListComponent,
    BlogDetailsComponent,
    BlogSortComponent
  ],
  imports: [
    CommonModule,
    BlogRoutingModule,
    FormsModule, 
    ReactiveFormsModule,
    // Plugins
    NgxSwiperModule,
    NgxOptimizedImageModule,  
    NgbTooltipModule,
    InlineSVGModule, 
    CoreModule
  ],
  schemas: [CUSTOM_ELEMENTS_SCHEMA], 
})
export class BlogModule { }
