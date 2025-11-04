import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';

import { 
  PaginatorState,
  GroupingState,
  SortState,
  ISortView,
  IGroupingView,
  IFilterView,
} from '../../../../shared';

import { 
  // Services
  PostService,
  Paging,
 } from '../../../../core';

@Component({
  selector: 'app-blog-list',
  templateUrl: './blog-list.component.html'
})
export class BlogListComponent
  implements 
  OnInit, 
  OnDestroy,
  ISortView,
  IGroupingView,
  IFilterView {

  isLoading?: boolean = false;

  selectedCategory = 0;

  paginator!: PaginatorState;
  grouping!: GroupingState;
  sorting!: SortState;  

  private subscriptions: Subscription[] = [];
  
  constructor(
    // Services
    public posts: PostService,
  ) {}  

  ngOnInit(): void {   
    //this.selectedCategory = 0;     
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

  // search
  searchForm() {
    const filter: Paging = new Paging();
    filter['search_string'] = '';
    this.filter( filter );
  }

  search(search_string: string) {
    this.posts.patchState({ search_string });
  }   

  // sorting
  sort(column: string) {
    const sorting = this.sorting;
    const isActiveColumn = sorting.column === column;
    if (!isActiveColumn) {
      sorting.column = column;
      sorting.direction = 'desc';
    } else {
      sorting.direction = sorting.direction === 'asc' ? 'desc' : 'asc';
    }
    this.posts.patchState({ sorting });
  }

  // pagination
  paginate(paginator: PaginatorState) {
    this.posts.patchState({ paginator });
  }      
    

  ngOnDestroy(): void {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
  }    
  
}
