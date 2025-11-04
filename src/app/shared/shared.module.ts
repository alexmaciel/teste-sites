import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

// 3rd-Party plugins variables
import { InlineSVGModule } from 'ng-inline-svg-2';
import { NgSelectModule } from '@ng-select/ng-select';
import { NgbModalModule } from '@ng-bootstrap/ng-bootstrap';
import { QuillModule } from 'ngx-quill';
// Translate
import { LocalizeRouterModule } from '@gilsdav/ngx-translate-router';
import { TranslateModule } from '@ngx-translate/core';

// Pipes
import { 
  TruncatetextPipe,
  DateLocalePipe,
  SafePipe
} from './pipes';
// Directive
import { 
  PageScrollDirective
} from './directives';
// Components
import { 
  CustomersComponent,
  BlogComponent,
  VideoComponent,
  // Map
  LocationComponent,
  // Request
  RequestComponent,
  FormComponent,
} from './components';

import { NgxSwiperModule } from './plugins/swiper-slider';
import { NgxYouTubePlayerModule } from './plugins/youtube-player';
import { NgxOptimizedImageModule } from './plugins/optimized-image';

// Pagination
import { NgPagination } from './helpers/paginator/ng-pagination/ng-pagination.component';
import { PaginatorComponent } from './helpers/paginator/paginator.component';


@NgModule({
  declarations: [
    // Pipes
    TruncatetextPipe,
    DateLocalePipe,
    SafePipe,   
    // Directives
    PageScrollDirective,
    // Components
    CustomersComponent,
    VideoComponent, 
    BlogComponent,
    // Map
    LocationComponent,  
    // Request
    RequestComponent,
    FormComponent,     
    // Pagination
    NgPagination,
    PaginatorComponent,      
  ],
  imports: [
    CommonModule,
    FormsModule, 
    ReactiveFormsModule,
    // Translate
    TranslateModule,
    LocalizeRouterModule, 
    NgSelectModule,
    QuillModule,
    InlineSVGModule,  
    NgbModalModule,     
    // Plugins
    NgxSwiperModule,  
    NgxYouTubePlayerModule,
    NgxOptimizedImageModule,           
  ],
  exports: [
    // Pipes
    TruncatetextPipe,
    DateLocalePipe,
    SafePipe, 
    // Directives
    PageScrollDirective,    
    // Components
    CustomersComponent,
    VideoComponent, 
    BlogComponent,
    // Map
    LocationComponent,  
    // Request
    RequestComponent,
    FormComponent,    
    // Pagination
    NgPagination,
    PaginatorComponent,   
    // Translate
    TranslateModule,
    LocalizeRouterModule,          
  ],
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})
export class SharedModule { }
