import { ChangeDetectionStrategy, Component, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';

import { LocalizeRouterService } from '@gilsdav/ngx-translate-router';
import { SwiperOptions } from 'swiper/types';

import { 
  PaginatorState,
  GroupingState,
  SortState,
  IGroupingView,
  IFilterView,
} from '../../helpers';

import { 
  Paging, 
  PostService 
} from '../../../core';

@Component({
  selector: 'app-blog',
  templateUrl: './blog.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    encapsulation: ViewEncapsulation.None,
})
export class BlogComponent implements
  OnInit,
  OnDestroy,
  IFilterView,
  IGroupingView {

  isLoading?: boolean = false;

  selectedCategory = 0;

  paginator!: PaginatorState;
  grouping!: GroupingState;
  sorting!: SortState;  

  private subscriptions: Subscription[] = [];
  
  constructor(
    private router: Router,
    private localize: LocalizeRouterService,    
    // Services
    public posts: PostService,
  ) {}  

  ngOnInit(): void {   
    this.selectedCategory = 0;     
    this.filterForm();

    const sb = this.posts.isLoading$.subscribe(res => this.isLoading = res);
    this.subscriptions.push(sb);
    this.paginator = this.posts.paginator;
    this.grouping = this.posts.grouping;
    this.sorting = this.posts.sorting;

    this.sorting.column = 'date';
    this.sorting.direction = 'desc';

    this.posts.fetch();      
  }  

  // filtration
  filterForm() {
    const filter: Paging = new Paging();
    filter['category_id'] = this.selectedCategory;
    filter['search_string'] = '';
    this.filter( filter );
  }

  filter(filter: Paging) {
    this.posts.patchState( filter );
  }  

  routeToPage(path?: string) {
    const translatedPath = this.localize.translateRoute(`${path}`);

    this.router.navigate([translatedPath]).then(() => {
      // console.log(`After navigation I am on: ${translatedPath}`)
     }); 
  }  

  ngOnDestroy(): void {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
  }  

  config: SwiperOptions = {
    slidesPerView: "auto",
    spaceBetween: 14,
    grabCursor: true,
    centeredSlides: false,
    keyboard: true,
    freeMode: true,
    navigation: false,
    pagination: {
      enabled: true,
      type: 'bullets',
      el: '.blog-pagination'
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
  }
}
