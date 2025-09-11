import { Component, Input, OnDestroy, OnInit } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { debounceTime, distinctUntilChanged, Subscription } from 'rxjs';

import { 
  CategoryService, 
  Paging
} from '../../../../core';

import { BlogListComponent } from '../blog-list/blog-list.component';

@Component({
  selector: 'app-blog-categories',
  templateUrl: './blog-categories.component.html'
})
export class BlogCategoriesComponent implements OnInit, OnDestroy {
  @Input() posts!: BlogListComponent;

  searchGroup!: FormGroup;
  
  selectedCategory = 0;

  private subscriptions: Subscription[] = [];
  
  constructor(
    private fb: FormBuilder,
    // Services
    public categories: CategoryService,
  ) { }

  ngOnInit(): void {
    this.searchForm();

    const sb = this.categories.getCategories().pipe(
    ).subscribe();
    this.subscriptions.push(sb);   
  }

  // search
  searchForm() {
    this.searchGroup = this.fb.group({
      search_string: [''],
    });
    const searchEvent = this.searchGroup.controls['search_string'].valueChanges
      .pipe(
        /*
        The user can type quite quickly in the input box, and that could trigger a lot of server requests. With this operator,
        we are limiting the amount of server requests emitted to a maximum of one every 150ms
        */
        debounceTime(150),
        distinctUntilChanged()
      )
      .subscribe((val) => this.posts.search(val));
    this.subscriptions.push(searchEvent);    
  }  

  onSelectCategory(categoryid: number) {
    const filter: Paging = new Paging();
    this.selectedCategory = categoryid;
    if (this.selectedCategory) {
      filter['category_id'] = this.selectedCategory;
    } else {
      filter['category_id'] = 0;
    }  
    this.posts.filter(filter);        
  }

  ngOnDestroy(): void {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
  } 
}
