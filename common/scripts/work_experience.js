function WorkExperience(_id, _industry, _from, _to, _place, _role, _work_summary, _reason_for_leaving) {
    this.record_id = _id; 
    this.industry = _industry; 
    this.from = _from; 
    this.to = _to; 
    this.place = _place; 
    this.role = _role; 
    this.work_summary = _work_summary;
    this.reason_for_leaving = _reason_for_leaving;
    this.deleted = false;
}
