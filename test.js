// Handler Abstract Class
class Approver {
  setNext(approver) {
    this.nextApprover = approver;
    return approver; // enable chaining
  }
  handleRequest(amount) {
    throw new Error("Not implemented");
  }
}

class Manager extends Approver {
  constructor() {
    super();
    this.count = 0;
  }

  handleRequest(amount) {
    if (amount <= 1000) {
      console.log(`${amount} : Manager approved`);
      this.count++;
    } else if (this.nextApprover) {
      this.nextApprover.handleRequest(amount);
    } else {
      console.log(`${amount} : Request denied`);
    }
  }
}

class Direktur extends Approver {
  constructor() {
    super();
    this.count = 0;
  }

  handleRequest(amount) {
    if (amount > 1000) {
      console.log(`${amount} : Direktur approved`);
      this.count++;
    } else if (this.nextApprover) {
      this.nextApprover.handleRequest(amount);
    } else {
      console.log(`${amount} : Request denied`);
    }
  }
}

// Object Data Setup Chain
const manager = new Manager();
const direktur = new Direktur();
manager.setNext(direktur);

// Test Request
const testRequest = [200, 800, 1000, 1200, 8000, 900, 3200];

testRequest.forEach(amount => {
  manager.handleRequest(amount);
});

// Summary
console.log("\n=== Summary Approval ===");
console.log(`Total handled by Manager : ${manager.count}`);
console.log(`Total handled by Direktur: ${direktur.count}`);